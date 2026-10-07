<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
requireNotSuperAdmin();

$pdo   = getDB();
$_page = 'peserta';
$pageTitle    = 'Data Peserta';
$pageSubtitle = 'Kelola data peserta seleksi';
$csrf = generateCSRF();

// ── POST HANDLER ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) { flash('Token tidak valid.', 'error'); header('Location: peserta.php'); exit; }

    $action = $_POST['action'] ?? '';

    if ($action === 'tambah' || $action === 'edit') {
        $id     = (int)($_POST['id'] ?? 0);
        $nama   = sanitize($_POST['nama'] ?? '');
        $jk     = in_array($_POST['jk']??'', ['L','P']) ? $_POST['jk'] : 'L';
        $sekolah= sanitize($_POST['asal_sekolah'] ?? '');
        // Nomor peserta: manual saat edit, auto saat tambah
        $nomor  = $action === 'edit' ? sanitize($_POST['nomor_peserta'] ?? '') : '';
        $err    = [];
        if (!$nama)              $err[] = 'Nama wajib diisi.';
        if (strlen($nama) > 150) $err[] = 'Nama terlalu panjang.';
        if ($action === 'edit' && !$nomor) $err[] = 'Nomor peserta wajib diisi saat edit.';

        if (empty($err)) {
            if ($action === 'tambah') {
                // ── Auto-generate nomor peserta ──
                $tahun  = getSetting('tahun_seleksi', date('Y'));
                $prefix = getSetting('prefix_nomor', 'PSK');
                // Ambil nomor urut terakhir tahun ini
                $lastNo = $pdo->query(
                    "SELECT nomor_peserta FROM peserta WHERE nomor_peserta LIKE '{$prefix}-{$tahun}-%'
                     ORDER BY id DESC LIMIT 1"
                )->fetchColumn();
                if ($lastNo) {
                    $parts = explode('-', $lastNo);
                    $urut  = (int)end($parts) + 1;
                } else {
                    $urut = 1;
                }
                $nomor = $prefix . '-' . $tahun . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

                $pdo->prepare("INSERT INTO peserta (nama,nomor_peserta,jenis_kelamin,asal_sekolah,is_active,created_at) VALUES (?,?,?,?,1,NOW())")
                    ->execute([$nama,$nomor,$jk,$sekolah]);
                catatLog('TAMBAH_PESERTA', "Nama=$nama No=$nomor");
                flash("Peserta <strong>$nama</strong> berhasil ditambahkan dengan nomor <strong>$nomor</strong>.");
            } else {
                // Cek duplikat nomor saat edit (kecuali nomor milik sendiri)
                $cek = $pdo->prepare("SELECT id FROM peserta WHERE nomor_peserta=? AND id!=? AND is_active=1");
                $cek->execute([$nomor, $id]);
                if ($cek->fetch()) { flash('Nomor peserta sudah digunakan peserta lain.', 'error'); header('Location: peserta.php'); exit; }

                $pdo->prepare("UPDATE peserta SET nama=?,nomor_peserta=?,jenis_kelamin=?,asal_sekolah=?,updated_at=NOW() WHERE id=? AND is_active=1")
                    ->execute([$nama,$nomor,$jk,$sekolah,$id]);
                catatLog('EDIT_PESERTA', "ID=$id Nama=$nama No=$nomor");
                flash("Data peserta <strong>$nama</strong> berhasil diperbarui.");
            }
        } else {
            flash(implode(' ', $err), 'error');
        }
        header('Location: peserta.php'); exit;

    } elseif ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        // Soft delete
        $s = $pdo->prepare("SELECT nama FROM peserta WHERE id=? LIMIT 1");
        $s->execute([$id]);
        $p = $s->fetch();
        if ($p) {
            $pdo->prepare("UPDATE peserta SET is_active=0,updated_at=NOW() WHERE id=?")->execute([$id]);
            // Hapus hasil KNN terkait
            $pdo->prepare("DELETE FROM hasil_knn WHERE id_peserta=?")->execute([$id]);
            catatLog('HAPUS_PESERTA', "ID=$id Nama={$p['nama']}");
            flash("Peserta {$p['nama']} berhasil dihapus.");
        }
        header('Location: peserta.php'); exit;

    } elseif ($action === 'import') {
        // Import CSV
        if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
            flash('File gagal diupload.', 'error'); header('Location: peserta.php'); exit;
        }
        $tmp = $_FILES['file_csv']['tmp_name'];
        $rows = []; $ok = 0; $skip = 0;
        if (($fh = fopen($tmp, 'r')) !== false) {
            $header = fgetcsv($fh, 0, ',') ?: fgetcsv($fh, 0, ';');
            while (($row = fgetcsv($fh, 0, ',')) !== false) {
                if (count($row) < 3) { $skip++; continue; }
                $nama   = trim($row[0] ?? '');
                $jk     = strtoupper(trim($row[1] ?? 'L'));
                $sekolah2 = trim($row[2] ?? '');
                // nomor auto-generate
                $tahunI  = getSetting('tahun_seleksi', date('Y'));
                $prefixI = getSetting('prefix_nomor', 'PSK');
                $lastNoI = $pdo->query(
                    "SELECT nomor_peserta FROM peserta WHERE nomor_peserta LIKE '{$prefixI}-{$tahunI}-%' ORDER BY id DESC LIMIT 1"
                )->fetchColumn();
                if ($lastNoI) { $parts2=explode('-',$lastNoI); $urutI=(int)end($parts2)+1; } else { $urutI=1+$ok; }
                $nomor = $prefixI.'-'.$tahunI.'-'.str_pad($urutI,3,'0',STR_PAD_LEFT);
                $sekolah= trim($row[3] ?? '');
                if (!$nama) { $skip++; continue; }
                if (!in_array($jk, ['L','P'])) $jk = 'L';
                // Cek duplikat, skip jika ada
                $pdo->prepare("INSERT INTO peserta (nama,nomor_peserta,jenis_kelamin,asal_sekolah,is_active,created_at) VALUES (?,?,?,?,1,NOW())")
                    ->execute([$nama,$nomor,$jk,$sekolah2]);
                $ok++;
            }
            fclose($fh);
        }
        catatLog('IMPORT_PESERTA', "Berhasil=$ok Skip=$skip");
        flash("Import selesai: $ok peserta ditambahkan, $skip dilewati.");
        header('Location: peserta.php'); exit;
    }
}

// ── GET DATA ──────────────────────────────────────────────
$pesertaList = $pdo->query("
    SELECT p.*,
           hk.status_kelulusan,
           CASE WHEN p.nilai_pip IS NOT NULL AND p.nilai_tiu IS NOT NULL
                     AND p.tinggi_badan IS NOT NULL AND p.push_up IS NOT NULL
                THEN 1 ELSE 0 END AS nilai_lengkap
    FROM peserta p
    LEFT JOIN hasil_knn hk ON hk.id_peserta = p.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC, p.id DESC
")->fetchAll();

$totalL      = count(array_filter($pesertaList, fn($r) => $r['jenis_kelamin']==='L'));
$totalP      = count(array_filter($pesertaList, fn($r) => $r['jenis_kelamin']==='P'));
$totalSemua  = count($pesertaList);
$nilaiLengkap= count(array_filter($pesertaList, fn($r) => $r['nilai_lengkap'] == 1));
$belumNilai  = $totalSemua - $nilaiLengkap;
$totalLulus  = count(array_filter($pesertaList, fn($r) => $r['status_kelulusan']==='Lulus'));
$totalTidak  = count(array_filter($pesertaList, fn($r) => $r['status_kelulusan']==='Tidak Lulus'));

// Data edit (jika ada ?edit=id)
$editData = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM peserta WHERE id=? AND is_active=1 LIMIT 1");
    $s->execute([(int)$_GET['edit']]);
    $editData = $s->fetch();
}

include __DIR__ . '/../includes/layout.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div>
    <h2 class="page-title">
      <span class="icon"><i class="fas fa-users"></i></span>
      Data Peserta
    </h2>
    <p class="page-subtitle">Kelola data peserta seleksi Paskibraka</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <button class="btn btn-secondary btn-sm" onclick="openModal('modalImport')">
      <i class="fas fa-file-csv"></i> Import CSV
    </button>
    <button class="btn btn-secondary btn-sm" onclick="downloadTemplate()">
      <i class="fas fa-download"></i> Template
    </button>
    <button class="btn btn-primary" onclick="openModal('modalTambah')">
      <i class="fas fa-plus"></i> Tambah Peserta
    </button>
  </div>
</div>

<!-- Statistik Peserta -->
<div class="stat-grid">
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-users" style="color:#3b82f6;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#111827;line-height:1;"><?= $totalSemua ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Total Peserta</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-mars" style="color:#2563eb;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#2563eb;line-height:1;"><?= $totalL ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Laki-laki</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#fdf4ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-venus" style="color:#a855f7;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#a855f7;line-height:1;"><?= $totalP ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Perempuan</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-circle-check" style="color:#10b981;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#10b981;line-height:1;"><?= $nilaiLengkap ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Nilai Lengkap</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-circle-exclamation" style="color:#f59e0b;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#f59e0b;line-height:1;"><?= $belumNilai ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Belum Lengkap</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-trophy" style="color:#059669;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#059669;line-height:1;"><?= $totalLulus ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Lulus KNN</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-circle-xmark" style="color:#ef4444;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#ef4444;line-height:1;"><?= $totalTidak ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Tidak Lulus KNN</div>
    </div>
  </div>
</div>

<!-- Tabel -->
<div class="card" style="padding:24px;">
  <div class="table-wrap">
    <table class="data-table" id="tblPeserta">
      <thead>
        <tr>
          <th style="width:40px;">#</th>
          <th>No. Peserta</th>
          <th>Nama</th>
          <th>JK</th>
          <th>Asal Sekolah</th>
          <th style="text-align:center;">Nilai</th>
          <th style="text-align:center;">Status KNN</th>
          <th style="text-align:center;width:100px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pesertaList as $i => $p): ?>
        <tr>
          <td style="color:#9ca3af;font-size:12px;"><?= $i+1 ?></td>
          <td><span class="font-mono" style="font-size:12px;color:#6b7280;"><?= htmlspecialchars($p['nomor_peserta']??'—') ?></span></td>
          <td style="font-weight:600;"><?= htmlspecialchars($p['nama']) ?></td>
          <td><?= jkLabel($p['jenis_kelamin']??'') ?></td>
          <td style="color:#6b7280;font-size:12.5px;"><?= htmlspecialchars($p['asal_sekolah']??'—') ?></td>
          <td style="text-align:center;">
            <?php if ($p['nilai_lengkap']): ?>
            <span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>
            <?php else: ?>
            <span class="badge badge-warning"><i class="fas fa-clock"></i> Belum</span>
            <?php endif; ?>
          </td>
          <td style="text-align:center;"><?= statusBadge($p['status_kelulusan'] ?? '') ?></td>
          <td style="text-align:center;">
            <div style="display:flex;gap:4px;justify-content:center;">
              <a href="nilai.php?peserta=<?= $p['id'] ?>" class="btn btn-sm btn-info btn-icon" title="Input Nilai">
                <i class="fas fa-clipboard"></i>
              </a>
              <button class="btn btn-sm btn-secondary btn-icon" title="Edit"
                onclick='editPeserta(<?= json_encode($p) ?>)'>
                <i class="fas fa-pencil"></i>
              </button>
              <button class="btn btn-sm btn-danger btn-icon" title="Hapus"
                onclick="hapusPeserta(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nama'])) ?>')">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── MODAL TAMBAH / EDIT ── -->
<div class="modal-backdrop" id="modalTambah">
  <div class="modal">
    <div class="modal-header">
      <h4 class="modal-title" id="modalTambahTitle">Tambah Peserta</h4>
      <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" id="formPeserta">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" id="formAction" value="tambah">
        <input type="hidden" name="id" id="formId" value="">

        <div class="form-group">
            <label class="form-label">Nama Lengkap <span class="required">*</span></label>
            <input type="text" name="nama" id="fNama" class="form-control" placeholder="Nama lengkap peserta" required maxlength="150">
          </div>

          <!-- Nomor Peserta: auto saat tambah, editable saat edit -->
          <div class="form-group" id="groupNomor" style="display:none;">
            <label class="form-label">Nomor Peserta</label>
            <input type="text" name="nomor_peserta" id="fNomor" class="form-control" maxlength="30">
            <div style="font-size:10.5px;color:#9ca3af;margin-top:3px;">Hanya muncul saat edit — saat tambah baru, nomor digenerate otomatis.</div>
          </div>

          <!-- Preview nomor otomatis saat mode Tambah -->
          <div class="form-group" id="groupNomorAuto" style="display:block;">
            <label class="form-label">Nomor Peserta</label>
            <div class="form-control" style="background:#f9fafb;color:#9ca3af;display:flex;align-items:center;gap:8px;font-size:13px;">
              <i class="fas fa-magic" style="color:#7c3aed;"></i>
              <span>Digenerate otomatis &nbsp;·&nbsp; Format: <strong><?= getSetting('prefix_nomor','PSK') . '-' . getSetting('tahun_seleksi', date('Y')) ?>-NNN</strong></span>
            </div>
            <div style="font-size:10.5px;color:#9ca3af;margin-top:3px;">Nomor urut akan diberikan secara otomatis setelah disimpan.</div>
          </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Jenis Kelamin <span class="required">*</span></label>
            <select name="jk" id="fJK" class="form-control form-select" required>
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Asal Sekolah</label>
            <input type="text" name="asal_sekolah" id="fSekolah" class="form-control" placeholder="Nama sekolah" maxlength="200">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL IMPORT CSV ── -->
<div class="modal-backdrop" id="modalImport">
  <div class="modal">
    <div class="modal-header">
      <h4 class="modal-title">Import Peserta via CSV</h4>
      <button class="modal-close" onclick="closeModal('modalImport');clearImportPeserta()"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data" id="formImportPeserta">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="import">
        <input type="file" name="file_csv" id="filePesertaInput" accept=".csv,.xlsx,.xls" style="display:none;" required>

        <!-- Info format -->
        <div class="alert alert-info" style="margin-bottom:16px;">
          <i class="fas fa-info-circle"></i>
          <div>Format kolom CSV: <strong>Nama, JK (L/P), Asal Sekolah</strong><br>
          Nomor peserta digenerate <strong>otomatis</strong>. Baris pertama boleh berupa header (akan dilewati).</div>
        </div>

        <!-- Drop Zone -->
        <div id="dropZonePeserta"
             onclick="document.getElementById('filePesertaInput').click()"
             ondragover="event.preventDefault();this.classList.add('dz-over')"
             ondragleave="this.classList.remove('dz-over')"
             ondrop="handleDropPeserta(event)"
             style="border:2px dashed #6ee7b7;background:#f0fdf4;border-radius:14px;padding:22px 20px;display:flex;align-items:center;gap:16px;cursor:pointer;transition:all .2s;">
          <div id="dzPesertaIcon" style="width:48px;height:48px;border-radius:12px;background:#059669;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-cloud-upload-alt" style="color:#fff;font-size:20px;"></i>
          </div>
          <div id="dzPesertaText" style="flex:1;">
            <p style="font-size:13.5px;font-weight:700;color:#065f46;margin:0 0 2px;">Klik atau seret file ke sini</p>
            <p style="font-size:12px;color:#6b7280;margin:0;">Format: .csv, .xlsx, .xls — Maks 5 MB</p>
          </div>
          <div id="dzPesertaClear" style="display:none;">
            <button type="button" onclick="event.stopPropagation();clearImportPeserta()"
                    style="border:none;background:#fef2f2;color:#dc2626;border-radius:8px;padding:5px 10px;cursor:pointer;font-size:12px;">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalImport');clearImportPeserta()">Batal</button>
        <button type="submit" id="btnImportPeserta" class="btn btn-primary" disabled style="opacity:.5;cursor:not-allowed;">
          <i class="fas fa-upload"></i> Import
        </button>
      </div>
    </form>
  </div>
</div>

<style>
#dropZonePeserta.dz-over     { border-color:#059669; background:#dcfce7; transform:scale(1.01); }
#dropZonePeserta.dz-has-file { border-color:#059669; border-style:solid; background:#f0fdf4; }
</style>

<script>
document.getElementById('filePesertaInput').addEventListener('change', function(){
  setImportPesertaFile(this.files[0]);
});
function handleDropPeserta(e) {
  e.preventDefault();
  document.getElementById('dropZonePeserta').classList.remove('dz-over');
  const file = e.dataTransfer.files[0];
  if (!file) return;
  const ext = file.name.split('.').pop().toLowerCase();
  if (!['csv','xlsx','xls'].includes(ext)) { alert('Format tidak didukung. Gunakan .csv, .xlsx, atau .xls'); return; }
  const dt = new DataTransfer(); dt.items.add(file);
  document.getElementById('filePesertaInput').files = dt.files;
  setImportPesertaFile(file);
}
function setImportPesertaFile(file) {
  if (!file) return;
  document.getElementById('dropZonePeserta').classList.add('dz-has-file');
  document.getElementById('dzPesertaIcon').innerHTML = '<i class="fas fa-file-csv" style="color:#fff;font-size:20px;"></i>';
  document.getElementById('dzPesertaText').innerHTML =
    `<p style="font-size:13.5px;font-weight:700;color:#065f46;margin:0 0 2px;">${file.name}</p>
     <p style="font-size:12px;color:#6b7280;margin:0;">${(file.size/1024).toFixed(1)} KB &nbsp;·&nbsp; Siap diimport</p>`;
  document.getElementById('dzPesertaClear').style.display = 'block';
  const btn = document.getElementById('btnImportPeserta');
  btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = 'pointer';
}
function clearImportPeserta() {
  document.getElementById('filePesertaInput').value = '';
  const dz = document.getElementById('dropZonePeserta');
  dz.classList.remove('dz-has-file','dz-over');
  document.getElementById('dzPesertaIcon').innerHTML = '<i class="fas fa-cloud-upload-alt" style="color:#fff;font-size:20px;"></i>';
  document.getElementById('dzPesertaText').innerHTML =
    `<p style="font-size:13.5px;font-weight:700;color:#065f46;margin:0 0 2px;">Klik atau seret file ke sini</p>
     <p style="font-size:12px;color:#6b7280;margin:0;">Format: .csv, .xlsx, .xls — Maks 5 MB</p>`;
  document.getElementById('dzPesertaClear').style.display = 'none';
  const btn = document.getElementById('btnImportPeserta');
  btn.disabled = true; btn.style.opacity = '.5'; btn.style.cursor = 'not-allowed';
}
</script>

<!-- Form Hapus -->
<form id="formHapus" method="POST" style="display:none;">
  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
  <input type="hidden" name="action" value="hapus">
  <input type="hidden" name="id" id="hapusId">
</form>

<?php
$extraJs = '<script src="' . BASE_URL . '/assets/js/peserta.js"></script>';
include __DIR__ . '/../includes/layout_end.php';
?>
