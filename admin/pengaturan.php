<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
requireNotSuperAdmin();

$pdo   = getDB();
$_page = 'pengaturan';
$pageTitle    = 'Pengaturan Sistem';
$pageSubtitle = 'Konfigurasi aplikasi dan parameter seleksi';
$csrf = generateCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) { flash('Token tidak valid.', 'error'); header('Location: pengaturan.php'); exit; }
    $keys = ['nama_kegiatan','tahun_seleksi','nama_instansi','kota','nama_kepala','nip_kepala','k_value','kuota_lulus_laki','kuota_lulus_perempuan'];
    foreach ($keys as $k) {
        if (isset($_POST[$k])) setSetting($k, sanitize($_POST[$k]));
    }
    // Ganti password jika diisi
    if (!empty($_POST['password_baru'])) {
        $passBaru    = $_POST['password_baru'];
        $konfirmasi  = $_POST['konfirmasi_password'] ?? '';
        $passLama    = $_POST['password_lama'] ?? '';
        $id          = $_SESSION['admin_id'] ?? 0;

        // Validasi: cek password lama
        $row = $pdo->prepare("SELECT password FROM admins WHERE id=? LIMIT 1");
        $row->execute([$id]);
        $adm = $row->fetch();
        if (!$adm || !password_verify($passLama, $adm['password'])) {
            flash('Password lama tidak sesuai. Perubahan password dibatalkan.', 'error');
            header('Location: pengaturan.php'); exit;
        }
        // Validasi: konfirmasi harus sama
        if ($passBaru !== $konfirmasi) {
            flash('Konfirmasi password tidak cocok. Perubahan password dibatalkan.', 'error');
            header('Location: pengaturan.php'); exit;
        }
        // Validasi: minimal 6 karakter
        if (strlen($passBaru) < 6) {
            flash('Password baru minimal 6 karakter.', 'error');
            header('Location: pengaturan.php'); exit;
        }
        $hash = password_hash($passBaru, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE admins SET password=? WHERE id=?")->execute([$hash,$id]);
        catatLog('GANTI_PASSWORD', 'Admin mengganti password');
    }
    catatLog('SIMPAN_PENGATURAN', 'Pengaturan sistem diperbarui');
    flash('Pengaturan berhasil disimpan.');
    header('Location: pengaturan.php'); exit;
}

$s = fn($k,$d='') => getSetting($k,$d);
include __DIR__ . '/../includes/layout.php';
?>
<div class="page-header">
  <div>
    <h2 class="page-title"><span class="icon"><i class="fas fa-cog"></i></span>Pengaturan Sistem</h2>
    <p class="page-subtitle">Konfigurasi aplikasi <?= APP_NAME ?></p>
  </div>
</div>

<form method="POST" id="formPengaturan">
  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;">

    <!-- Informasi Kegiatan -->
    <div class="card" style="padding:26px 24px;">
      <div class="form-section"><i class="fas fa-calendar-star"></i> Informasi Kegiatan</div>
      <div class="form-group"><label class="form-label">Nama Kegiatan</label><input type="text" name="nama_kegiatan" class="form-control" value="<?= htmlspecialchars($s('nama_kegiatan','Seleksi Paskibraka')) ?>"></div>
      <div class="form-group"><label class="form-label">Tahun Seleksi</label><input type="number" name="tahun_seleksi" class="form-control" value="<?= htmlspecialchars($s('tahun_seleksi',date('Y'))) ?>" min="2000" max="2099"></div>
      <div class="form-group"><label class="form-label">Nama Instansi</label><input type="text" name="nama_instansi" class="form-control" value="<?= htmlspecialchars($s('nama_instansi','Dinas Pendidikan')) ?>"></div>
      <div class="form-group" style="margin:0;"><label class="form-label">Kota / Kabupaten</label><input type="text" name="kota" class="form-control" value="<?= htmlspecialchars($s('kota','')) ?>"></div>
    </div>

    <!-- Laporan & TTD -->
    <div class="card" style="padding:26px 24px;">
      <div class="form-section"><i class="fas fa-signature"></i> Tanda Tangan Laporan</div>
      <div class="form-group"><label class="form-label">Nama Kepala / Pejabat</label><input type="text" name="nama_kepala" class="form-control" value="<?= htmlspecialchars($s('nama_kepala','')) ?>"></div>
      <div class="form-group" style="margin:0;"><label class="form-label">NIP</label><input type="text" name="nip_kepala" class="form-control" value="<?= htmlspecialchars($s('nip_kepala','')) ?>"></div>
    </div>

    <!-- Parameter KNN -->
    <div class="card" style="padding:26px 24px;">
      <div class="form-section"><i class="fas fa-brain"></i> Parameter KNN</div>
      <div class="form-group">
        <label class="form-label">Nilai K Default</label>
        <select name="k_value" class="form-control form-select">
          <?php for ($ki=1;$ki<=15;$ki+=2): ?><option value="<?=$ki?>" <?=(int)$s('k_value','3')===$ki?'selected':''?>>K = <?=$ki?></option><?php endfor; ?>
        </select>
        <p class="text-muted" style="margin-top:5px;">Nilai K yang digunakan saat proses KNN. Disarankan bilangan ganjil.</p>
      </div>
      <div class="form-row form-row-2">
        <div class="form-group"><label class="form-label">Kuota Lulus Laki-laki</label><input type="number" name="kuota_lulus_laki" class="form-control" min="0" value="<?= $s('kuota_lulus_laki','35') ?>"></div>
        <div class="form-group" style="margin:0;"><label class="form-label">Kuota Lulus Perempuan</label><input type="number" name="kuota_lulus_perempuan" class="form-control" min="0" value="<?= $s('kuota_lulus_perempuan','35') ?>"></div>
      </div>
    </div>

    <!-- Keamanan -->
    <div class="card" style="padding:26px 24px;">
      <div class="form-section"><i class="fas fa-lock"></i> Keamanan Akun</div>
      <div class="form-group">
        <label class="form-label">Password Lama</label>
        <div style="position:relative;">
          <input type="password" name="password_lama" id="passLama" class="form-control" placeholder="Masukkan password lama..." autocomplete="current-password" style="padding-right:40px;">
          <button type="button" onclick="togglePassVis('passLama','eyeLama')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;"><i class="fas fa-eye" id="eyeLama"></i></button>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password Baru</label>
        <div style="position:relative;">
          <input type="password" name="password_baru" id="passBaru" class="form-control" placeholder="Minimal 6 karakter..." autocomplete="new-password" style="padding-right:40px;" oninput="cekKekuatan(this.value)">
          <button type="button" onclick="togglePassVis('passBaru','eyeBaru')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;"><i class="fas fa-eye" id="eyeBaru"></i></button>
        </div>
        <!-- Indikator kekuatan password -->
        <div id="passStrengthWrap" style="margin-top:8px;display:none;">
          <div style="height:4px;background:#f3f4f6;border-radius:99px;overflow:hidden;">
            <div id="passStrengthBar" style="height:100%;width:0%;border-radius:99px;transition:all .3s;"></div>
          </div>
          <p id="passStrengthLabel" style="font-size:11.5px;margin:4px 0 0;font-weight:600;"></p>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Konfirmasi Password Baru</label>
        <div style="position:relative;">
          <input type="password" name="konfirmasi_password" id="passKonfirm" class="form-control" placeholder="Ulangi password baru..." autocomplete="new-password" style="padding-right:40px;" oninput="cekKonfirmasi()">
          <button type="button" onclick="togglePassVis('passKonfirm','eyeKonfirm')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;"><i class="fas fa-eye" id="eyeKonfirm"></i></button>
        </div>
        <p id="passMatchLabel" style="font-size:11.5px;margin:4px 0 0;font-weight:600;display:none;"></p>
      </div>
      <div class="alert alert-warning" style="margin-top:8px;">
        <i class="fas fa-triangle-exclamation"></i> Kosongkan semua field password jika tidak ingin mengganti password.
      </div>
    </div>

  </div>
  <div style="margin-top:24px;display:flex;justify-content:space-between;align-items:center;padding:18px 24px;background:#fff;border-radius:var(--radius-lg);border:1px solid var(--border);box-shadow:var(--shadow-sm);">
    <p style="margin:0;font-size:12.5px;color:var(--text-4);"><i class="fas fa-info-circle" style="margin-right:5px;"></i>Perubahan akan langsung berlaku setelah disimpan.</p>
    <button type="submit" class="btn btn-primary" style="padding:11px 28px;font-size:13.5px;">
      <i class="fas fa-save"></i> Simpan Semua Pengaturan
    </button>
  </div>
</form>

<script>
function togglePassVis(inputId, iconId) {
  const inp = document.getElementById(inputId);
  const ico = document.getElementById(iconId);
  if (!inp) return;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  ico.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function cekKekuatan(val) {
  const wrap  = document.getElementById('passStrengthWrap');
  const bar   = document.getElementById('passStrengthBar');
  const label = document.getElementById('passStrengthLabel');
  if (!val) { wrap.style.display='none'; return; }
  wrap.style.display = 'block';
  let score = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const lvl = [
    { w:'20%', color:'#ef4444', text:'Sangat Lemah' },
    { w:'40%', color:'#f97316', text:'Lemah' },
    { w:'60%', color:'#f59e0b', text:'Cukup' },
    { w:'80%', color:'#84cc16', text:'Kuat' },
    { w:'100%',color:'#10b981', text:'Sangat Kuat' },
  ][Math.max(0,score-1)];
  bar.style.width      = lvl.w;
  bar.style.background = lvl.color;
  label.textContent    = lvl.text;
  label.style.color    = lvl.color;
  cekKonfirmasi();
}

function cekKonfirmasi() {
  const baru    = document.getElementById('passBaru')?.value || '';
  const konfirm = document.getElementById('passKonfirm')?.value || '';
  const label   = document.getElementById('passMatchLabel');
  if (!label || !konfirm) { label.style.display='none'; return; }
  label.style.display = 'block';
  if (baru === konfirm) {
    label.textContent  = '✓ Password cocok';
    label.style.color  = '#10b981';
    document.getElementById('passKonfirm').style.borderColor = '#10b981';
  } else {
    label.textContent  = '✗ Password tidak cocok';
    label.style.color  = '#ef4444';
    document.getElementById('passKonfirm').style.borderColor = '#ef4444';
  }
}

// Validasi sebelum submit: jika ada isi password baru, pastikan cocok
document.querySelector('form')?.addEventListener('submit', function(e) {
  const baru    = document.getElementById('passBaru')?.value || '';
  const konfirm = document.getElementById('passKonfirm')?.value || '';
  const lama    = document.getElementById('passLama')?.value || '';
  if (baru) {
    if (!lama) {
      e.preventDefault();
      Swal.fire({
        html: `<div style="text-align:center;padding:6px 0;">
          <div style="width:52px;height:52px;border-radius:50%;background:#fff7ed;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <i class="fas fa-lock" style="font-size:20px;color:#f59e0b;"></i>
          </div>
          <h3 style="font-size:17px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Password Lama Diperlukan</h3>
          <p style="font-size:13px;color:#9b8b8b;margin:0;">Masukkan password lama untuk verifikasi identitas.</p>
        </div>`,
        confirmButtonText: '<i class="fas fa-check"></i>&nbsp;Mengerti',
        confirmButtonColor: '#f59e0b',
        width: 340, padding: '24px', borderRadius: '18px',
        backdrop: 'rgba(15,10,10,0.5)',
        customClass: { popup: 'swal-popup-custom' },
      }); return;
    }
    if (baru !== konfirm) {
      e.preventDefault();
      Swal.fire({
        html: `<div style="text-align:center;padding:6px 0;">
          <div style="width:52px;height:52px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <i class="fas fa-key" style="font-size:20px;color:#dc2626;"></i>
          </div>
          <h3 style="font-size:17px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Password Tidak Cocok</h3>
          <p style="font-size:13px;color:#9b8b8b;margin:0;">Password baru dan konfirmasi password tidak sama. Periksa kembali.</p>
        </div>`,
        confirmButtonText: '<i class="fas fa-check"></i>&nbsp;Perbaiki',
        confirmButtonColor: '#dc2626',
        width: 340, padding: '24px', borderRadius: '18px',
        backdrop: 'rgba(15,10,10,0.5)',
        customClass: { popup: 'swal-popup-custom' },
      }); return;
    }
    if (baru.length < 6) {
      e.preventDefault();
      Swal.fire({
        html: `<div style="text-align:center;padding:6px 0;">
          <div style="width:52px;height:52px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <i class="fas fa-shield-alt" style="font-size:20px;color:#dc2626;"></i>
          </div>
          <h3 style="font-size:17px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Password Terlalu Pendek</h3>
          <p style="font-size:13px;color:#9b8b8b;margin:0;">Password baru minimal harus 6 karakter.</p>
        </div>`,
        confirmButtonText: '<i class="fas fa-check"></i>&nbsp;Mengerti',
        confirmButtonColor: '#dc2626',
        width: 340, padding: '24px', borderRadius: '18px',
        backdrop: 'rgba(15,10,10,0.5)',
        customClass: { popup: 'swal-popup-custom' },
      }); return;
    }
  }
});
</script>
<?php include __DIR__ . '/../includes/layout_end.php'; ?>
