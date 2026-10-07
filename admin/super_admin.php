<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireSuperAdmin();

$pdo       = getDB();
$_page     = 'super_admin';
$pageTitle = 'Manajemen Admin';
$csrf      = generateCSRF();

// ── POST HANDLER ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        flash('Token keamanan tidak valid.', 'error');
        header('Location: super_admin.php'); exit;
    }
    $action = $_POST['action'] ?? '';

    // TAMBAH ADMIN
    if ($action === 'tambah_admin') {
        $nama     = sanitize($_POST['nama']     ?? '');
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password']           ?? '';
        $konfirm  = $_POST['konfirmasi']         ?? '';

        $err = [];
        if (strlen($nama) < 3)     $err[] = 'Nama minimal 3 karakter.';
        if (strlen($username) < 3) $err[] = 'Username minimal 3 karakter.';
        if (!preg_match('/^[a-z0-9_]+$/', $username)) $err[] = 'Username hanya huruf kecil, angka, underscore.';
        if (strlen($password) < 6) $err[] = 'Password minimal 6 karakter.';
        if ($password !== $konfirm) $err[] = 'Konfirmasi password tidak cocok.';

        if (empty($err)) {
            $cek = $pdo->prepare("SELECT id FROM admins WHERE username=? LIMIT 1");
            $cek->execute([$username]);
            if ($cek->fetch()) $err[] = "Username '$username' sudah digunakan.";
        }
        if (empty($err)) {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
            $pdo->prepare("INSERT INTO admins (nama,username,password,role,is_active) VALUES (?,?,?,'admin',1)")
                ->execute([$nama, $username, $hash]);
            catatLog('SUPER_TAMBAH_ADMIN', "Tambah admin: $username ($nama)");
            flash("Admin <strong>$nama</strong> berhasil ditambahkan.");
        } else {
            $_SESSION['validation_errors'] = $err;
            $_SESSION['validation_context'] = 'tambah_admin';
            flash(implode(' ', $err), 'error');
        }
        header('Location: super_admin.php'); exit;

    // EDIT ADMIN
    } elseif ($action === 'edit_admin') {
        $idAdmin  = (int)($_POST['id_admin']  ?? 0);
        $nama     = sanitize($_POST['nama']    ?? '');
        $username = sanitize($_POST['username'] ?? '');

        $err = [];
        if (!$idAdmin)             $err[] = 'ID admin tidak valid.';
        if (strlen($nama) < 3)     $err[] = 'Nama minimal 3 karakter.';
        if (strlen($username) < 3) $err[] = 'Username minimal 3 karakter.';
        if (!preg_match('/^[a-z0-9_]+$/', $username)) $err[] = 'Username hanya huruf kecil, angka, underscore.';

        if (empty($err)) {
            $cek = $pdo->prepare("SELECT id FROM admins WHERE username=? AND id!=? LIMIT 1");
            $cek->execute([$username, $idAdmin]);
            if ($cek->fetch()) $err[] = "Username '$username' sudah digunakan admin lain.";
        }
        if (empty($err)) {
            $pdo->prepare("UPDATE admins SET nama=?,username=?,updated_at=NOW() WHERE id=?")
                ->execute([$nama, $username, $idAdmin]);
            catatLog('SUPER_EDIT_ADMIN', "Edit admin ID=$idAdmin nama=$nama username=$username");
            flash("Data admin <strong>$nama</strong> berhasil diperbarui.");
        } else {
            $_SESSION['validation_errors'] = $err;
            $_SESSION['validation_context'] = 'edit_admin';
            flash(implode(' ', $err), 'error');
        }
        header('Location: super_admin.php'); exit;

    // GANTI PASSWORD
    } elseif ($action === 'ganti_password') {
        $idAdmin = (int)($_POST['id_admin']           ?? 0);
        $newPass = $_POST['new_password']              ?? '';
        $konfirm = $_POST['konfirmasi_password']       ?? '';

        $err = [];
        if (!$idAdmin)              $err[] = 'ID admin tidak valid.';
        if (strlen($newPass) < 6)   $err[] = 'Password minimal 6 karakter.';
        if ($newPass !== $konfirm)  $err[] = 'Konfirmasi password tidak cocok.';

        if (empty($err)) {
            $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost'=>12]);
            $pdo->prepare("UPDATE admins SET password=?,updated_at=NOW() WHERE id=?")
                ->execute([$hash, $idAdmin]);
            $s = $pdo->prepare("SELECT nama FROM admins WHERE id=? LIMIT 1");
            $s->execute([$idAdmin]);
            $nama = $s->fetchColumn();
            catatLog('SUPER_GANTI_PASS', "Ganti password admin: $nama ID=$idAdmin");
            flash("Password admin <strong>$nama</strong> berhasil diperbarui.");
        } else {
            $_SESSION['validation_errors'] = $err;
            $_SESSION['validation_context'] = 'ganti_password';
            flash(implode(' ', $err), 'error');
        }
        header('Location: super_admin.php'); exit;

    // TOGGLE AKTIF
    } elseif ($action === 'toggle_aktif') {
        $idAdmin = (int)($_POST['id_admin'] ?? 0);
        if ($idAdmin === (int)($_SESSION['admin_id'] ?? 0)) {
            flash('Tidak dapat menonaktifkan akun sendiri.', 'error');
        } else {
            $row = $pdo->prepare("SELECT is_active,nama,role FROM admins WHERE id=? LIMIT 1");
            $row->execute([$idAdmin]); $row = $row->fetch();
            if ($row) {
                if ($row['role'] === 'superadmin') {
                    flash('Akun Super Admin tidak bisa dinonaktifkan.', 'error');
                } else {
                    $ns = $row['is_active'] ? 0 : 1;
                    $pdo->prepare("UPDATE admins SET is_active=?,updated_at=NOW() WHERE id=?")->execute([$ns, $idAdmin]);
                    catatLog('SUPER_TOGGLE', "ID=$idAdmin ".($ns?'Aktif':'Nonaktif'));
                    flash('Status admin <strong>'.$row['nama'].'</strong> diubah ke '.($ns?'Aktif':'Nonaktif').'.');
                }
            } else { flash('Admin tidak ditemukan.', 'error'); }
        }
        header('Location: super_admin.php'); exit;

    // HAPUS ADMIN
    } elseif ($action === 'hapus_admin') {
        $idAdmin = (int)($_POST['id_admin'] ?? 0);
        if ($idAdmin === (int)($_SESSION['admin_id'] ?? 0)) {
            flash('Tidak dapat menghapus akun sendiri.', 'error');
        } else {
            $row = $pdo->prepare("SELECT nama,role FROM admins WHERE id=? LIMIT 1");
            $row->execute([$idAdmin]); $row = $row->fetch();
            if ($row && $row['role'] !== 'superadmin') {
                $pdo->prepare("DELETE FROM admins WHERE id=?")->execute([$idAdmin]);
                catatLog('SUPER_HAPUS', "Hapus admin: {$row['nama']} ID=$idAdmin");
                flash("Admin <strong>{$row['nama']}</strong> berhasil dihapus.", 'warning');
            } elseif ($row && $row['role'] === 'superadmin') {
                flash('Akun Super Admin tidak bisa dihapus.', 'error');
            } else { flash('Admin tidak ditemukan.', 'error'); }
        }
        header('Location: super_admin.php'); exit;
    }
}

// ── AMBIL DATA ────────────────────────────────────────────────────────────────
// Baca validation errors jika ada
$validationErrors  = $_SESSION['validation_errors']  ?? [];
$validationContext = $_SESSION['validation_context'] ?? '';
unset($_SESSION['validation_errors'], $_SESSION['validation_context']);

$adminList = $pdo->query("
    SELECT id,nama,username,role,is_active,last_login,created_at,updated_at
    FROM admins ORDER BY role DESC, nama ASC
")->fetchAll();

$myId = (int)($_SESSION['admin_id'] ?? 0);
include __DIR__ . '/../includes/layout.php';
?>

<!-- Page Header -->
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
  <div>
    <h2 style="font-size:15px;font-weight:800;color:#111827;margin:0;display:flex;align-items:center;gap:8px;">
      <span style="width:34px;height:34px;background:linear-gradient(135deg,#7c3aed,#5b21b6);border-radius:10px;display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-user-shield" style="color:#fff;font-size:14px;"></i>
      </span>
      Manajemen Akun Admin
    </h2>
    <p style="font-size:12px;color:#9ca3af;margin:4px 0 0;">Total <?= count($adminList) ?> akun terdaftar</p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('modalTambahAdmin')">
    <i class="fas fa-plus"></i> Tambah Admin Baru
  </button>
</div>

<!-- Warning banner -->
<div style="margin-bottom:18px;padding:12px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;display:flex;align-items:flex-start;gap:10px;">
  <i class="fas fa-triangle-exclamation" style="color:#d97706;margin-top:1px;flex-shrink:0;"></i>
  <div style="font-size:12.5px;color:#92400e;">
    <strong>Halaman Super Admin</strong> — Hanya Super Admin yang dapat mengakses halaman ini.
    Perubahan di sini berdampak langsung pada akses seluruh sistem.
  </div>
</div>

<!-- Tabel Admin -->
<div class="card" style="padding:24px;">
  <div class="table-wrap">
    <table class="data-table" id="tblAdmin">
      <thead>
        <tr>
          <th style="width:36px;">#</th>
          <th>Nama</th>
          <th>Username</th>
          <th style="text-align:center;">Role</th>
          <th style="text-align:center;">Status</th>
          <th>Login Terakhir</th>
          <th>Dibuat</th>
          <th style="text-align:center;width:150px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($adminList as $i => $a):
          $isMe = ($a['id'] === $myId);
          $isSA = ($a['role'] === 'superadmin');
        ?>
        <tr style="<?= $isMe ? 'background:#fefce8;' : '' ?>">
          <td style="color:#9ca3af;font-size:12px;"><?= $i + 1 ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:9px;">
              <div style="width:34px;height:34px;border-radius:10px;background:<?= $isSA ? 'linear-gradient(135deg,#7c3aed,#5b21b6)' : '#f3f4f6' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:13px;font-weight:700;color:<?= $isSA ? '#fff' : '#374151' ?>;"><?= strtoupper(mb_substr($a['nama'],0,1)) ?></span>
              </div>
              <div>
                <div style="font-size:13px;font-weight:700;color:#111827;">
                  <?= htmlspecialchars($a['nama']) ?>
                  <?= $isMe ? '<span style="font-size:10px;background:#fef3c7;color:#d97706;padding:1px 7px;border-radius:10px;font-weight:600;margin-left:4px;">(Anda)</span>' : '' ?>
                </div>
                <div style="font-size:11px;color:#9ca3af;">ID: <?= $a['id'] ?></div>
              </div>
            </div>
          </td>
          <td style="font-family:monospace;font-size:13px;color:#374151;"><?= htmlspecialchars($a['username']) ?></td>
          <td style="text-align:center;">
            <?php if ($isSA): ?>
              <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:linear-gradient(135deg,#f5f3ff,#ede9fe);color:#5b21b6;border:1px solid #ddd6fe;display:inline-flex;align-items:center;gap:4px;">
                <i class="fas fa-crown" style="font-size:9px;"></i> Super Admin
              </span>
            <?php else: ?>
              <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:#f0fdf4;color:#059669;border:1px solid #bbf7d0;display:inline-flex;align-items:center;gap:4px;">
                <i class="fas fa-user-cog" style="font-size:9px;"></i> Admin
              </span>
            <?php endif; ?>
          </td>
          <td style="text-align:center;">
            <?php if ($a['is_active']): ?>
              <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:#f0fdf4;color:#059669;">
                <i class="fas fa-circle" style="font-size:7px;"></i> Aktif
              </span>
            <?php else: ?>
              <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:#fef2f2;color:#dc2626;">
                <i class="fas fa-circle" style="font-size:7px;"></i> Nonaktif
              </span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:#6b7280;"><?= $a['last_login'] ? date('d/m/Y H:i', strtotime($a['last_login'])) : 'Belum pernah' ?></td>
          <td style="font-size:12px;color:#9ca3af;"><?= formatTanggal($a['created_at']) ?></td>
          <td style="text-align:center;">
            <div style="display:inline-flex;gap:5px;align-items:center;flex-wrap:nowrap;">
              <!-- Edit -->
              <button type="button" title="Edit Data"
                      style="width:32px;height:32px;border-radius:8px;border:none;cursor:pointer;
                             background:#eff6ff;color:#2563eb;font-size:13px;
                             display:flex;align-items:center;justify-content:center;transition:all .15s;"
                      onmouseover="this.style.background='#dbeafe'"
                      onmouseout="this.style.background='#eff6ff'"
                      onclick="bukaEditAdmin(<?= $a['id'] ?>, <?= htmlspecialchars(json_encode($a['nama'])) ?>, <?= htmlspecialchars(json_encode($a['username'])) ?>)">
                <i class="fas fa-pen"></i>
              </button>
              <!-- Ganti Password -->
              <button type="button" title="Ganti Password"
                      style="width:32px;height:32px;border-radius:8px;border:none;cursor:pointer;
                             background:#fffbeb;color:#d97706;font-size:13px;
                             display:flex;align-items:center;justify-content:center;transition:all .15s;"
                      onmouseover="this.style.background='#fef3c7'"
                      onmouseout="this.style.background='#fffbeb'"
                      onclick="bukaGantiPassword(<?= $a['id'] ?>, <?= htmlspecialchars(json_encode($a['nama'])) ?>)">
                <i class="fas fa-key"></i>
              </button>
              <?php if (!$isMe && !$isSA): ?>
              <!-- Toggle Aktif -->
              <form method="POST" style="display:contents;">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="toggle_aktif">
                <input type="hidden" name="id_admin" value="<?= $a['id'] ?>">
                <button type="submit" title="<?= $a['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>"
                        style="width:32px;height:32px;border-radius:8px;border:none;cursor:pointer;font-size:13px;
                               background:<?= $a['is_active'] ? '#fef2f2' : '#f0fdf4' ?>;
                               color:<?= $a['is_active'] ? '#dc2626' : '#059669' ?>;
                               display:flex;align-items:center;justify-content:center;transition:all .15s;"
                        onmouseover="this.style.opacity='.75'"
                        onmouseout="this.style.opacity='1'">
                  <i class="fas fa-<?= $a['is_active'] ? 'ban' : 'check' ?>"></i>
                </button>
              </form>
              <!-- Hapus -->
              <button type="button" title="Hapus Admin"
                      style="width:32px;height:32px;border-radius:8px;border:none;cursor:pointer;
                             background:#fef2f2;color:#dc2626;font-size:13px;
                             display:flex;align-items:center;justify-content:center;transition:all .15s;"
                      onmouseover="this.style.background='#fee2e2'"
                      onmouseout="this.style.background='#fef2f2'"
                      onclick="hapusAdmin(<?= $a['id'] ?>, <?= htmlspecialchars(json_encode($a['nama'])) ?>)">
                <i class="fas fa-trash"></i>
              </button>
              <?php else: ?>
              <span style="font-size:11px;color:#d1d5db;letter-spacing:.05em;">— —</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ══ MODAL: Tambah Admin ══ -->
<div class="modal-backdrop" id="modalTambahAdmin">
  <div class="modal">
    <div class="modal-header">
      <h4 class="modal-title"><i class="fas fa-user-plus" style="color:#059669;margin-right:6px;"></i> Tambah Admin Baru</h4>
      <button class="modal-close" onclick="closeModal('modalTambahAdmin')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="tambah_admin">
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span class="required">*</span></label>
          <input type="text" name="nama" class="form-control" required placeholder="Cth: Budi Santoso" minlength="3">
        </div>
        <div class="form-group">
          <label class="form-label">Username <span class="required">*</span></label>
          <input type="text" name="username" class="form-control" required placeholder="huruf_kecil_angka" pattern="[a-z0-9_]+" minlength="3">
          <div style="font-size:10.5px;color:#9ca3af;margin-top:3px;">Hanya huruf kecil, angka, dan underscore</div>
        </div>
        <div class="form-group">
          <label class="form-label">Password <span class="required">*</span></label>
          <div style="position:relative;">
            <input type="password" name="password" id="tPass" class="form-control" required placeholder="Min. 6 karakter" minlength="6" style="padding-right:40px;">
            <button type="button" onclick="togglePass('tPass',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:none;color:#9ca3af;cursor:pointer;padding:0;"><i class="fas fa-eye"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
          <div style="position:relative;">
            <input type="password" name="konfirmasi" id="tKonfirm" class="form-control" required placeholder="Ulangi password" style="padding-right:40px;" oninput="cekMatch('tPass','tKonfirm','matchT')">
            <button type="button" onclick="togglePass('tKonfirm',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:none;color:#9ca3af;cursor:pointer;padding:0;"><i class="fas fa-eye"></i></button>
          </div>
          <div id="matchT" style="font-size:10.5px;margin-top:3px;"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modalTambahAdmin')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Tambah Admin</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL: Edit Admin ══ -->
<div class="modal-backdrop" id="modalEditAdmin">
  <div class="modal">
    <div class="modal-header">
      <h4 class="modal-title"><i class="fas fa-user-edit" style="color:#1d4ed8;margin-right:6px;"></i> Edit Data Admin</h4>
      <button class="modal-close" onclick="closeModal('modalEditAdmin')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="edit_admin">
        <input type="hidden" name="id_admin" id="editId">
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span class="required">*</span></label>
          <input type="text" name="nama" id="editNama" class="form-control" required minlength="3" placeholder="Nama lengkap">
        </div>
        <div class="form-group">
          <label class="form-label">Username <span class="required">*</span></label>
          <input type="text" name="username" id="editUsername" class="form-control" required pattern="[a-z0-9_]+" minlength="3" placeholder="huruf_kecil_angka">
          <div style="font-size:10.5px;color:#9ca3af;margin-top:3px;">Hanya huruf kecil, angka, dan underscore</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modalEditAdmin')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL: Ganti Password ══ -->
<div class="modal-backdrop" id="modalGantiPassword">
  <div class="modal">
    <div class="modal-header">
      <h4 class="modal-title"><i class="fas fa-key" style="color:#d97706;margin-right:6px;"></i> Ganti Password Admin</h4>
      <button class="modal-close" onclick="closeModal('modalGantiPassword')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="ganti_password">
        <input type="hidden" name="id_admin" id="gpId">
        <div style="padding:10px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;margin-bottom:16px;font-size:12.5px;color:#92400e;">
          <i class="fas fa-user" style="margin-right:6px;"></i>
          Mengganti password untuk: <strong id="gpNama"></strong>
        </div>
        <div class="form-group">
          <label class="form-label">Password Baru <span class="required">*</span></label>
          <div style="position:relative;">
            <input type="password" name="new_password" id="gpPass" class="form-control" required placeholder="Min. 6 karakter" minlength="6" style="padding-right:40px;" oninput="cekMatch('gpPass','gpKonfirm','matchGP')">
            <button type="button" onclick="togglePass('gpPass',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:none;color:#9ca3af;cursor:pointer;padding:0;"><i class="fas fa-eye"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Password Baru <span class="required">*</span></label>
          <div style="position:relative;">
            <input type="password" name="konfirmasi_password" id="gpKonfirm" class="form-control" required placeholder="Ulangi password baru" style="padding-right:40px;" oninput="cekMatch('gpPass','gpKonfirm','matchGP')">
            <button type="button" onclick="togglePass('gpKonfirm',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:none;color:#9ca3af;cursor:pointer;padding:0;"><i class="fas fa-eye"></i></button>
          </div>
          <div id="matchGP" style="font-size:10.5px;margin-top:3px;"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('modalGantiPassword')" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn" style="background:#d97706;color:#fff;font-weight:700;">
          <i class="fas fa-key"></i> Ganti Password
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Form hapus (hidden) -->
<form id="fHapus" method="POST" style="display:none;">
  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
  <input type="hidden" name="action" value="hapus_admin">
  <input type="hidden" name="id_admin" id="hapusId">
</form>

<script>
function bukaEditAdmin(id, nama, username) {
  document.getElementById('editId').value       = id;
  document.getElementById('editNama').value     = nama;
  document.getElementById('editUsername').value = username;
  openModal('modalEditAdmin');
}
function bukaGantiPassword(id, nama) {
  document.getElementById('gpId').value             = id;
  document.getElementById('gpNama').textContent      = nama;
  document.getElementById('gpPass').value            = '';
  document.getElementById('gpKonfirm').value         = '';
  document.getElementById('matchGP').innerHTML       = '';
  openModal('modalGantiPassword');
}
function hapusAdmin(id, nama) {
  Swal.fire({
    icon:'warning', title:'Hapus Admin?',
    html:'Akun admin <strong>'+nama+'</strong> akan dihapus permanen dari sistem.',
    showCancelButton:true,
    confirmButtonText:'<i class="fas fa-trash"></i> Hapus',
    cancelButtonText:'Batal',
    confirmButtonColor:'#b91c1c'
  }).then(function(r){
    if (r.isConfirmed) {
      document.getElementById('hapusId').value = id;
      document.getElementById('fHapus').submit();
    }
  });
}
function cekMatch(idA, idB, idInfo) {
  var a  = document.getElementById(idA).value;
  var b  = document.getElementById(idB).value;
  var el = document.getElementById(idInfo);
  if (!b) { el.innerHTML=''; return; }
  if (a===b) el.innerHTML='<span style="color:#059669"><i class="fas fa-check-circle"></i> Password cocok</span>';
  else       el.innerHTML='<span style="color:#dc2626"><i class="fas fa-times-circle"></i> Password tidak cocok</span>';
}
function togglePass(inputId, btn) {
  var inp  = document.getElementById(inputId);
  var icon = btn.querySelector('i');
  if (inp.type==='password') { inp.type='text';     icon.className='fas fa-eye-slash'; }
  else                       { inp.type='password'; icon.className='fas fa-eye'; }
}

// Notifikasi validasi dari server
<?php if (!empty($validationErrors)): ?>
document.addEventListener('DOMContentLoaded', function() {
  var errors = <?= json_encode($validationErrors, JSON_UNESCAPED_UNICODE) ?>;
  var ctx    = <?= json_encode($validationContext) ?>;
  var listHtml = errors.map(function(e) {
    return '<li style="text-align:left;padding:3px 0;font-size:13px;color:#374151;">' +
           '<i class="fas fa-exclamation-circle" style="color:#dc2626;margin-right:6px;font-size:11px;"></i>' + e + '</li>';
  }).join('');
  var titleMap = {
    'tambah_admin':    'Gagal Tambah Admin',
    'edit_admin':      'Gagal Edit Admin',
    'ganti_password':  'Gagal Ganti Password'
  };
  Swal.fire({
    html: '<div style="text-align:center;padding:4px 0 2px;">' +
      '<div style="width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(220,38,38,.2);">' +
        '<i class="fas fa-triangle-exclamation" style="font-size:22px;color:#dc2626;"></i>' +
      '</div>' +
      '<h3 style="font-size:16px;font-weight:800;color:#1a0a0a;margin:0 0 10px;font-family:\'Plus Jakarta Sans\',sans-serif;">' +
        (titleMap[ctx] || 'Data Tidak Valid') +
      '</h3>' +
      '<ul style="list-style:none;padding:0;margin:0 0 4px;background:#fef9f9;border:1px solid #fee2e2;border-radius:10px;padding:10px 14px;text-align:left;">' +
        listHtml +
      '</ul>' +
    '</div>',
    confirmButtonText: '<i class="fas fa-check"></i>&nbsp;Mengerti',
    confirmButtonColor: '#dc2626',
    width: 420, padding: '28px 28px 24px', borderRadius: '20px',
    backdrop: 'rgba(15,10,10,0.55)',
    customClass: { popup: 'swal-popup-custom' },
  }).then(function() {
    <?php if ($validationContext === 'tambah_admin'): ?>
    openModal('modalTambahAdmin');
    <?php elseif ($validationContext === 'edit_admin'): ?>
    openModal('modalEditAdmin');
    <?php elseif ($validationContext === 'ganti_password'): ?>
    openModal('modalGantiPassword');
    <?php endif; ?>
  });
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/layout_end.php'; ?>