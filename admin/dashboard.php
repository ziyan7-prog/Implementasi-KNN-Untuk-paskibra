<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
requireNotSuperAdmin();

$pdo   = getDB();
$_page = 'dashboard';
$pageTitle    = 'Dashboard';
$pageSubtitle = 'Ringkasan data seleksi ' . getSetting('tahun_seleksi', date('Y'));

try {
    $totalPeserta  = (int)$pdo->query("SELECT COUNT(*) FROM peserta WHERE is_active=1")->fetchColumn();
    $nilaiLengkap  = (int)$pdo->query("SELECT COUNT(*) FROM peserta WHERE is_active=1 AND nilai_pip IS NOT NULL AND nilai_tiu IS NOT NULL AND tinggi_badan IS NOT NULL AND push_up IS NOT NULL")->fetchColumn();
    $sudahKNN      = (int)$pdo->query("SELECT COUNT(*) FROM hasil_knn")->fetchColumn();
    $totalLulus    = (int)$pdo->query("SELECT COUNT(*) FROM hasil_knn WHERE status_kelulusan='Lulus'")->fetchColumn();
    $totalTidak    = (int)$pdo->query("SELECT COUNT(*) FROM hasil_knn WHERE status_kelulusan='Tidak Lulus'")->fetchColumn();
    $lulusL        = (int)$pdo->query("SELECT COUNT(*) FROM hasil_knn hk JOIN peserta p ON p.id=hk.id_peserta WHERE hk.status_kelulusan='Lulus' AND p.jenis_kelamin='L'")->fetchColumn();
    $lulusP        = (int)$pdo->query("SELECT COUNT(*) FROM hasil_knn hk JOIN peserta p ON p.id=hk.id_peserta WHERE hk.status_kelulusan='Lulus' AND p.jenis_kelamin='P'")->fetchColumn();
    $tidakL        = (int)$pdo->query("SELECT COUNT(*) FROM hasil_knn hk JOIN peserta p ON p.id=hk.id_peserta WHERE hk.status_kelulusan='Tidak Lulus' AND p.jenis_kelamin='L'")->fetchColumn();
    $tidakP        = (int)$pdo->query("SELECT COUNT(*) FROM hasil_knn hk JOIN peserta p ON p.id=hk.id_peserta WHERE hk.status_kelulusan='Tidak Lulus' AND p.jenis_kelamin='P'")->fetchColumn();
    $totalDataLatih= (int)$pdo->query("SELECT COUNT(*) FROM data_latih")->fetchColumn();

    // Peserta terbaru
    $pesertaBaru = $pdo->query("SELECT nama,nomor_peserta,jenis_kelamin,asal_sekolah,created_at FROM peserta WHERE is_active=1 ORDER BY created_at DESC LIMIT 6")->fetchAll();

    // Distribusi sekolah (top 6)
    $distSekolah = $pdo->query("SELECT asal_sekolah, COUNT(*) as jml FROM peserta WHERE is_active=1 AND asal_sekolah IS NOT NULL GROUP BY asal_sekolah ORDER BY jml DESC LIMIT 6")->fetchAll();

    // Rata-rata nilai per kriteria
    $avgNilai = $pdo->query("SELECT
        ROUND(AVG(nilai_pip),1) as avg_pip, ROUND(AVG(nilai_tiu),1) as avg_tiu,
        ROUND(AVG(push_up),1) as avg_pu, ROUND(AVG(sit_up),1) as avg_su,
        ROUND(AVG(lari),1) as avg_lari, ROUND(AVG(pbb),1) as avg_pbb,
        ROUND(AVG(kepribadian),1) as avg_kep
        FROM peserta WHERE is_active=1")->fetch();

    // === DATA UNTUK SCATTER DOT PLOT KNN ===
    // Peserta uji: PIP vs TIU, colored by status KNN
    $scatterUji = $pdo->query("
        SELECT p.nama, p.nilai_pip, p.nilai_tiu, p.jenis_kelamin,
               hk.status_kelulusan, hk.vote_lulus, hk.vote_tidak_lulus, hk.skor_knn
        FROM peserta p
        LEFT JOIN hasil_knn hk ON hk.id_peserta = p.id
        WHERE p.is_active=1 AND p.nilai_pip IS NOT NULL AND p.nilai_tiu IS NOT NULL
        ORDER BY p.created_at DESC
    ")->fetchAll();

    // Data latih sebagai referensi titik
    $scatterLatih = $pdo->query("
        SELECT nama_referensi, nilai_pip, nilai_tiu, label FROM data_latih ORDER BY id ASC
    ")->fetchAll();

    // Distribusi skor jarak KNN (untuk histogram)
    $skorDist = $pdo->query("
        SELECT ROUND(skor_knn,2) as skor, status_kelulusan
        FROM hasil_knn WHERE skor_knn IS NOT NULL ORDER BY skor_knn ASC
    ")->fetchAll();

    // Vote distribution
    $voteDist = $pdo->query("
        SELECT vote_lulus, vote_tidak_lulus, status_kelulusan, COUNT(*) as jml
        FROM hasil_knn GROUP BY vote_lulus, vote_tidak_lulus, status_kelulusan
    ")->fetchAll();

} catch (Throwable $e) {
    $totalPeserta=$nilaiLengkap=$sudahKNN=$totalLulus=$totalTidak=$lulusL=$lulusP=$tidakL=$tidakP=$totalDataLatih=0;
    $pesertaBaru=[]; $distSekolah=[]; $avgNilai=[];
    $scatterUji=[]; $scatterLatih=[]; $skorDist=[]; $voteDist=[];
}

$namaKegiatan = getSetting('nama_kegiatan', 'Seleksi Paskibraka');
$tahunSel     = getSetting('tahun_seleksi', date('Y'));
$kuotaL       = (int)getSetting('kuota_lulus_laki', '35');
$kuotaP       = (int)getSetting('kuota_lulus_perempuan', '35');
$belumKNN     = $totalPeserta - $sudahKNN;
$kDefault     = (int)getSetting('k_value', (string)KNN_K);

// Siapkan data scatter untuk JS
// Warna dot berdasarkan label KNN murni (vote), BUKAN status_kelulusan yg bisa diubah kuota
$dotLatihLulus  = []; $dotLatihTidak = [];
foreach ($scatterLatih as $d) {
    $pt = ['x'=>(float)$d['nilai_pip'],'y'=>(float)$d['nilai_tiu'],'label'=>$d['nama_referensi']];
    if ($d['label']==='Lulus') $dotLatihLulus[] = $pt;
    else                       $dotLatihTidak[] = $pt;
}
$dotUjiLulus = []; $dotUjiTidak = []; $dotUjiBelum = [];
foreach ($scatterUji as $p) {
    $vl  = (int)($p['vote_lulus']      ?? 0);
    $vtl = (int)($p['vote_tidak_lulus']?? 0);
    $pt  = ['x'=>(float)$p['nilai_pip'],'y'=>(float)$p['nilai_tiu'],'label'=>$p['nama'],
            'vl'=>$vl,'vtl'=>$vtl,'status'=>($p['status_kelulusan']??'')];
    // Klasifikasi warna: gunakan hasil vote KNN murni
    if ($p['status_kelulusan'] === null || $p['status_kelulusan'] === '') {
        $dotUjiBelum[] = $pt; // belum diproses KNN
    } elseif ($vl > $vtl) {
        $dotUjiLulus[] = $pt; // mayoritas vote Lulus → hijau
    } else {
        $dotUjiTidak[] = $pt; // mayoritas vote Tidak Lulus → merah
    }
}

$extraCss = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"><\/script>';
$extraJs  = ''; // Chart.js dimuat di head via extraCss
include __DIR__ . '/../includes/layout.php';
?>

<!-- ── STAT CARDS ─────────────────────────────────────────── -->
<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:22px;">
<?php
$cards = [
  ['fa-users','#1d4ed8','#eff6ff',$totalPeserta,'Total Peserta','peserta.php'],
  ['fa-clipboard-check','#059669','#f0fdf4',$nilaiLengkap,'Nilai Lengkap','nilai.php'],
  ['fa-brain','#7c3aed','#f5f3ff',$sudahKNN,'Sudah KNN','proses_knn.php'],
  ['fa-check-circle','#059669','#ecfdf5',$totalLulus,'Lulus','proses_knn.php'],
  ['fa-times-circle','#dc2626','#fef2f2',$totalTidak,'Tidak Lulus','proses_knn.php'],
  ['fa-database','#d97706','#fffbeb',$totalDataLatih,'Data Latih','data_latih.php'],
];
foreach ($cards as [$ico,$clr,$bg,$val,$lbl,$href]): ?>
<a href="<?=BASE_URL?>/admin/<?=$href?>" style="text-decoration:none;">
  <div class="card" style="padding:16px 18px;display:flex;align-items:center;gap:12px;transition:box-shadow .15s;cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.10)'" onmouseout="this.style.boxShadow=''">
    <div style="width:42px;height:42px;border-radius:11px;background:<?=$bg?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas <?=$ico?>" style="font-size:16px;color:<?=$clr?>;"></i>
    </div>
    <div>
      <div style="font-size:24px;font-weight:800;color:#111827;line-height:1;"><?=$val?></div>
      <div style="font-size:11px;color:#9ca3af;margin-top:2px;"><?=$lbl?></div>
    </div>
  </div>
</a>
<?php endforeach; ?>
</div>

<!-- ── PROGRESS SELEKSI ──────────────────────────────────── -->
<div class="card" style="padding:20px;margin-bottom:22px;">
  <h3 style="font-size:14px;font-weight:700;color:#111827;margin:0 0 14px;display:flex;align-items:center;gap:7px;">
    <i class="fas fa-chart-line" style="color:#9f1239;"></i> Progress Seleksi <?=htmlspecialchars($namaKegiatan)?> <?=$tahunSel?>
  </h3>
  <?php
  $steps = [
    ['Pendaftaran','Peserta Terdaftar',$totalPeserta,$totalPeserta,'#6366f1'],
    ['Tahap 1 &amp; 2 &amp; 3','Nilai Lengkap',$nilaiLengkap,$totalPeserta,'#3b82f6'],
    ['Proses KNN','Sudah Diklasifikasi',$sudahKNN,$totalPeserta,'#7c3aed'],
    ['Hasil Akhir','Dinyatakan Lulus',$totalLulus,$totalPeserta,'#10b981'],
  ];
  foreach ($steps as [$tahap,$sub,$val,$max,$clr]):
    $pct = $max > 0 ? round($val/$max*100) : 0;
  ?>
  <div style="margin-bottom:13px;">
    <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:4px;">
      <div>
        <span style="font-size:12.5px;font-weight:700;color:#374151;"><?=$tahap?></span>
        <span style="font-size:11px;color:#9ca3af;margin-left:6px;"><?=$sub?></span>
      </div>
      <span style="font-size:12.5px;font-weight:700;color:<?=$clr?>;"><?=$val?> <span style="color:#9ca3af;font-weight:400;">/ <?=$max?> (<?=$pct?>%)</span></span>
    </div>
    <div style="height:8px;background:#f3f4f6;border-radius:99px;overflow:hidden;">
      <div style="height:100%;width:<?=$pct?>%;background:<?=$clr?>;border-radius:99px;transition:width .6s;"></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── BARIS GRAFIK 1: Donut + Bar JK + Radar ─────────────── -->
<div class="dash-row-3" style="display:grid;grid-template-columns:1fr 1fr 1.3fr;gap:18px;margin-bottom:18px;">

  <!-- Donut: Kelulusan -->
  <div class="card" style="padding:20px;">
    <h3 style="font-size:13.5px;font-weight:700;color:#111827;margin:0 0 4px;display:flex;align-items:center;gap:6px;">
      <i class="fas fa-chart-pie" style="color:#9f1239;"></i> Hasil KNN
    </h3>
    <p style="font-size:11px;color:#9ca3af;margin:0 0 14px;">Distribusi klasifikasi peserta</p>
    <div style="position:relative;height:160px;">
      <canvas id="chartKelulusan"></canvas>
    </div>
    <div style="display:flex;justify-content:center;gap:12px;margin-top:12px;font-size:11px;">
      <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#10b981;border-radius:2px;display:inline-block;"></span>Lulus (<?=$totalLulus?>)</span>
      <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#ef4444;border-radius:2px;display:inline-block;"></span>Tidak Lulus (<?=$totalTidak?>)</span>
      <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#e5e7eb;border-radius:2px;display:inline-block;"></span>Belum (<?=$belumKNN?>)</span>
    </div>
  </div>

  <!-- Grouped Bar: Lulus per JK -->
  <div class="card" style="padding:20px;">
    <h3 style="font-size:13.5px;font-weight:700;color:#111827;margin:0 0 4px;display:flex;align-items:center;gap:6px;">
      <i class="fas fa-venus-mars" style="color:#3b82f6;"></i> Kelulusan per Jenis Kelamin
    </h3>
    <p style="font-size:11px;color:#9ca3af;margin:0 0 14px;">Laki-laki vs Perempuan</p>
    <div style="position:relative;height:160px;">
      <canvas id="chartJK"></canvas>
    </div>
    <div style="display:flex;justify-content:center;gap:12px;margin-top:12px;font-size:11px;">
      <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#3b82f6;border-radius:2px;display:inline-block;"></span>♂ L</span>
      <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#ec4899;border-radius:2px;display:inline-block;"></span>♀ P</span>
    </div>
  </div>

  <!-- Radar: Rata-rata Nilai -->
  <div class="card" style="padding:20px;">
    <h3 style="font-size:13.5px;font-weight:700;color:#111827;margin:0 0 4px;display:flex;align-items:center;gap:6px;">
      <i class="fas fa-star" style="color:#f59e0b;"></i> Rata-rata Nilai Peserta
    </h3>
    <p style="font-size:11px;color:#9ca3af;margin:0 0 10px;">Profil nilai rata-rata seluruh peserta</p>
    <div style="position:relative;height:175px;">
      <canvas id="chartRadar"></canvas>
    </div>
  </div>
</div>

<!-- ── SCATTER DOT PLOT KNN (UTAMA) ──────────────────────── -->
<div class="card" style="padding:22px;margin-bottom:18px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;flex-wrap:wrap;gap:10px;">
    <div>
      <h3 style="font-size:14px;font-weight:700;color:#111827;margin:0 0 4px;display:flex;align-items:center;gap:7px;">
        <i class="fas fa-braille" style="color:#7c3aed;"></i> Diagram Titik KNN — Ruang Fitur PIP × TIU
      </h3>
      <p style="font-size:11.5px;color:#9ca3af;margin:0;">
        Setiap titik merepresentasikan satu peserta/data latih di ruang dua dimensi. Warna menunjukkan hasil klasifikasi KNN.
      </p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;font-size:11px;">
      <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;background:#10b981;border-radius:50%;display:inline-block;border:2px solid #fff;box-shadow:0 0 0 1.5px #10b981;"></span>Peserta Lulus</span>
      <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;background:#ef4444;border-radius:50%;display:inline-block;border:2px solid #fff;box-shadow:0 0 0 1.5px #ef4444;"></span>Peserta Tidak Lulus</span>
      <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;background:#9ca3af;border-radius:50%;display:inline-block;"></span>Belum KNN</span>
      <span style="display:flex;align-items:center;gap:5px;"><span style="width:9px;height:9px;background:#a7f3d0;border-radius:2px;display:inline-block;border:1.5px solid #10b981;"></span>Latih Lulus</span>
      <span style="display:flex;align-items:center;gap:5px;"><span style="width:9px;height:9px;background:#fecaca;border-radius:2px;display:inline-block;border:1.5px solid #ef4444;"></span>Latih Tidak Lulus</span>
    </div>
  </div>
  <?php if ($sudahKNN === 0 && empty($scatterLatih)): ?>
  <div style="height:300px;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#9ca3af;">
    <i class="fas fa-braille" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
    <p style="margin:0;font-size:13px;font-weight:600;">Belum ada data untuk ditampilkan</p>
    <p style="margin:4px 0 0;font-size:12px;">Jalankan proses KNN atau tambahkan data latih terlebih dahulu.</p>
  </div>
  <?php else: ?>
  <div style="position:relative;height:340px;">
    <canvas id="chartScatterKNN"></canvas>
  </div>
  <p style="font-size:10.5px;color:#9ca3af;margin:8px 0 0;text-align:right;">
    Sumbu X = Nilai PIP &nbsp;|&nbsp; Sumbu Y = Nilai TIU &nbsp;|&nbsp; Kotak kecil = Data Latih &nbsp;|&nbsp; Lingkaran = Peserta Uji
  </p>
  <?php endif; ?>
</div>

<!-- ── BARIS GRAFIK 2: Kuota + Vote Distribution + Sekolah ── -->
<div class="dash-row-3" style="display:grid;grid-template-columns:1fr 1fr 1.4fr;gap:18px;margin-bottom:18px;">

  <!-- Kuota -->
  <div class="card" style="padding:20px;">
    <h3 style="font-size:13.5px;font-weight:700;color:#111827;margin:0 0 4px;display:flex;align-items:center;gap:6px;">
      <i class="fas fa-users-gear" style="color:#8b5cf6;"></i> Kuota Kelulusan
    </h3>
    <p style="font-size:11px;color:#9ca3af;margin:0 0 14px;">Target vs terpenuhi per gender</p>
    <div style="position:relative;height:160px;">
      <canvas id="chartKuota"></canvas>
    </div>
    <div style="display:flex;justify-content:center;gap:12px;margin-top:10px;font-size:11px;">
      <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#e5e7eb;border-radius:2px;display:inline-block;"></span>Target</span>
      <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#9f1239;border-radius:2px;display:inline-block;"></span>Terpenuhi</span>
    </div>
  </div>

  <!-- Vote Distribution Bubble Chart -->
  <div class="card" style="padding:20px;">
    <h3 style="font-size:13.5px;font-weight:700;color:#111827;margin:0 0 4px;display:flex;align-items:center;gap:6px;">
      <i class="fas fa-vote-yea" style="color:#059669;"></i> Distribusi Vote KNN
    </h3>
    <p style="font-size:11px;color:#9ca3af;margin:0 0 14px;">Sebaran jumlah vote mayoritas per peserta</p>
    <div style="position:relative;height:160px;">
      <canvas id="chartVoteDist"></canvas>
    </div>
    <div style="display:flex;justify-content:center;gap:12px;margin-top:10px;font-size:11px;">
      <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:rgba(16,185,129,.7);border-radius:2px;display:inline-block;"></span>Vote Lulus</span>
      <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:rgba(239,68,68,.7);border-radius:2px;display:inline-block;"></span>Vote Tidak Lulus</span>
    </div>
  </div>

  <!-- Distribusi Sekolah -->
  <div class="card" style="padding:20px;">
    <h3 style="font-size:13.5px;font-weight:700;color:#111827;margin:0 0 4px;display:flex;align-items:center;gap:6px;">
      <i class="fas fa-school" style="color:#6366f1;"></i> Distribusi Sekolah (Top <?=count($distSekolah)?>)
    </h3>
    <p style="font-size:11px;color:#9ca3af;margin:0 0 14px;">Jumlah peserta per asal sekolah</p>
    <div style="position:relative;height:160px;">
      <canvas id="chartSekolah"></canvas>
    </div>
  </div>
</div>

<!-- ── JARAK KNN CHART (Skor per Peserta) ────────────────── -->
<?php if ($sudahKNN > 0): ?>
<div class="card" style="padding:22px;margin-bottom:18px;">
  <h3 style="font-size:14px;font-weight:700;color:#111827;margin:0 0 4px;display:flex;align-items:center;gap:7px;">
    <i class="fas fa-ruler" style="color:#d97706;"></i> Distribusi Jarak Euclidean (Skor KNN)
  </h3>
  <p style="font-size:11.5px;color:#9ca3af;margin:0 0 14px;">
    Semakin kecil jarak ke tetangga terdekat, semakin mirip peserta dengan data latih. Titik hijau = Lulus, merah = Tidak Lulus.
  </p>
  <div style="position:relative;height:200px;">
    <canvas id="chartSkorKNN"></canvas>
  </div>
</div>
<?php endif; ?>

<!-- ── PESERTA TERBARU ────────────────────────────────────── -->
<div class="card" style="padding:22px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h3 style="font-size:14px;font-weight:700;color:#111827;margin:0;display:flex;align-items:center;gap:8px;">
      <i class="fas fa-user-clock" style="color:#9f1239;"></i> Peserta Terbaru Terdaftar
    </h3>
    <a href="<?=BASE_URL?>/admin/peserta.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
  </div>
  <?php if (empty($pesertaBaru)): ?>
  <div style="text-align:center;padding:32px;color:#9ca3af;">
    <i class="fas fa-users" style="font-size:32px;margin-bottom:10px;display:block;"></i>
    Belum ada peserta terdaftar.
  </div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>No. Peserta</th><th>Nama</th><th>JK</th><th>Sekolah</th><th>Terdaftar</th></tr></thead>
      <tbody>
        <?php foreach ($pesertaBaru as $p): ?>
        <tr>
          <td><span style="font-family:monospace;font-size:12px;color:#6b7280;"><?=htmlspecialchars($p['nomor_peserta']??'—')?></span></td>
          <td style="font-weight:600;color:#111827;"><?=htmlspecialchars($p['nama'])?></td>
          <td><?=jkLabel($p['jenis_kelamin']??'')?></td>
          <td style="color:#6b7280;font-size:12.5px;"><?=htmlspecialchars($p['asal_sekolah']??'—')?></td>
          <td style="font-size:12px;color:#9ca3af;"><?=formatTanggal($p['created_at']??null)?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<script>
window.addEventListener('load', function() {
// ── Data dari PHP ──────────────────────────────────────────
const D = {
  kelulusan: { lulus:<?=$totalLulus?>, tidak:<?=$totalTidak?>, belum:<?=$belumKNN?> },
  jk: { lulusL:<?=$lulusL?>, lulusP:<?=$lulusP?>, tidakL:<?=$tidakL?>, tidakP:<?=$tidakP?> },
  kuota: { targetL:<?=$kuotaL?>, targetP:<?=$kuotaP?>, terpL:<?=$lulusL?>, terpP:<?=$lulusP?> },
  sekolah: {
    labels: <?=json_encode(array_column($distSekolah,'asal_sekolah'))?>,
    data:   <?=json_encode(array_map('intval',array_column($distSekolah,'jml')))?>
  },
  avg: {
    pip:<?=(float)($avgNilai['avg_pip']??0)?>, tiu:<?=(float)($avgNilai['avg_tiu']??0)?>,
    pu:<?=(float)($avgNilai['avg_pu']??0)?>,   su:<?=(float)($avgNilai['avg_su']??0)?>,
    lari:<?=(float)($avgNilai['avg_lari']??0)?>, pbb:<?=(float)($avgNilai['avg_pbb']??0)?>,
    kep:<?=(float)($avgNilai['avg_kep']??0)?>
  },
  // Scatter KNN
  latihLulus: <?=json_encode($dotLatihLulus)?>,
  latihTidak: <?=json_encode($dotLatihTidak)?>,
  ujiLulus:   <?=json_encode($dotUjiLulus)?>,
  ujiTidak:   <?=json_encode($dotUjiTidak)?>,
  ujiBelum:   <?=json_encode($dotUjiBelum)?>,
  // Skor KNN per peserta
  skorKNN: <?=json_encode(array_map(function($r){ return ['skor'=>(float)$r['skor'],'status'=>$r['status_kelulusan']]; }, $skorDist))?>
};

Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.font.size   = 11;

// ── 1. Donut Kelulusan ─────────────────────────────────────
new Chart(document.getElementById('chartKelulusan'), {
  type: 'doughnut',
  data: {
    labels: ['Lulus','Tidak Lulus','Belum Diproses'],
    datasets: [{ data:[D.kelulusan.lulus,D.kelulusan.tidak,D.kelulusan.belum],
      backgroundColor:['#10b981','#ef4444','#e5e7eb'],
      borderWidth:2, borderColor:'#fff', hoverOffset:8 }]
  },
  options: { responsive:true, maintainAspectRatio:false, cutout:'65%',
    plugins:{ legend:{display:false},
      tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${ctx.parsed} peserta`}} } }
});

// ── 2. Grouped Bar: JK ────────────────────────────────────
new Chart(document.getElementById('chartJK'), {
  type:'bar',
  data:{
    labels:['Lulus','Tidak Lulus'],
    datasets:[
      {label:'♂ Laki-laki', data:[D.jk.lulusL,D.jk.tidakL], backgroundColor:'#3b82f6', borderRadius:6},
      {label:'♀ Perempuan', data:[D.jk.lulusP,D.jk.tidakP], backgroundColor:'#ec4899', borderRadius:6}
    ]
  },
  options:{responsive:true,maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'#f9fafb'}}}}
});

// ── 3. Radar ──────────────────────────────────────────────
new Chart(document.getElementById('chartRadar'), {
  type:'radar',
  data:{
    labels:['PIP','TIU','Push Up','Sit Up','Lari','PBB','Kepribadian'],
    datasets:[{
      label:'Rata-rata Peserta',
      data:[D.avg.pip,D.avg.tiu,D.avg.pu,D.avg.su,D.avg.lari,D.avg.pbb,D.avg.kep],
      backgroundColor:'rgba(159,18,57,.12)', borderColor:'#9f1239',
      borderWidth:2, pointBackgroundColor:'#9f1239', pointRadius:3, pointHoverRadius:5
    }]
  },
  options:{responsive:true,maintainAspectRatio:false,
    scales:{r:{beginAtZero:true,max:100,
      ticks:{stepSize:20,font:{size:9}},
      pointLabels:{font:{size:10,weight:'600'}},
      grid:{color:'#f3f4f6'},angleLines:{color:'#e5e7eb'}}},
    plugins:{legend:{display:false}}}
});

// ── 4. SCATTER DOT PLOT KNN ───────────────────────────────
(function(){
  const el = document.getElementById('chartScatterKNN');
  if (!el) return;

  // Custom point renderer: square = latih, circle = uji
  const squarePlugin = {
    id:'squarePoints',
    afterDatasetsDraw(chart){
      const ctx2 = chart.ctx;
      chart.data.datasets.forEach((ds,di)=>{
        if (!ds._isLatih) return;
        const meta = chart.getDatasetMeta(di);
        meta.data.forEach(pt=>{
          const {x,y} = pt.getCenterPoint();
          const r = 5;
          ctx2.save();
          ctx2.fillStyle   = ds.backgroundColor;
          ctx2.strokeStyle = ds.borderColor;
          ctx2.lineWidth   = 1.5;
          ctx2.beginPath();
          ctx2.rect(x-r, y-r, r*2, r*2);
          ctx2.fill();
          ctx2.stroke();
          ctx2.restore();
        });
      });
    }
  };

  new Chart(el, {
    type:'scatter',
    plugins:[squarePlugin],
    data:{
      datasets:[
        // Data latih (kotak kecil)
        { label:'Data Latih — Lulus',
          data: D.latihLulus, _isLatih:true,
          backgroundColor:'rgba(167,243,208,.8)', borderColor:'#10b981',
          pointRadius:0, pointHoverRadius:0 },
        { label:'Data Latih — Tidak Lulus',
          data: D.latihTidak, _isLatih:true,
          backgroundColor:'rgba(254,202,202,.8)', borderColor:'#ef4444',
          pointRadius:0, pointHoverRadius:0 },
        // Peserta (lingkaran besar) — render Tidak Lulus & Belum dulu, Lulus paling atas
        { label:'Peserta — Belum KNN',
          data: D.ujiBelum,
          backgroundColor:'rgba(156,163,175,.7)', borderColor:'#fff',
          borderWidth:2, pointRadius:7, pointHoverRadius:9,
          pointStyle:'circle' },
        { label:'Peserta — Tidak Lulus',
          data: D.ujiTidak,
          backgroundColor:'rgba(239,68,68,.85)', borderColor:'#fff',
          borderWidth:2, pointRadius:9, pointHoverRadius:11,
          pointStyle:'circle' },
        { label:'Peserta — Lulus',
          data: D.ujiLulus,
          backgroundColor:'rgba(16,185,129,.85)', borderColor:'#fff',
          borderWidth:2, pointRadius:9, pointHoverRadius:11,
          pointStyle:'circle' },
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{
        legend:{display:false},
        tooltip:{
          callbacks:{
            label:ctx=>{
              const d=ctx.raw;
              const lines=[`  ${d.label||'Data Latih'}`];
              lines.push(`  PIP: ${d.x} | TIU: ${d.y}`);
              if (d.vl!==undefined) lines.push(`  Vote Lulus: ${d.vl} | Tidak: ${d.vtl}`);
              if (d.status) lines.push(`  Status Akhir: ${d.status}`);
              return lines;
            }
          },
          backgroundColor:'rgba(17,24,39,.9)', titleColor:'#fff',
          bodyColor:'rgba(255,255,255,.85)', padding:10, cornerRadius:10
        }
      },
      scales:{
        x:{title:{display:true,text:'Nilai PIP',font:{weight:'700',size:11}},
           min:0,max:100,grid:{color:'#f3f4f6'},ticks:{stepSize:10}},
        y:{title:{display:true,text:'Nilai TIU',font:{weight:'700',size:11}},
           min:0,max:100,grid:{color:'#f3f4f6'},ticks:{stepSize:10}}
      }
    }
  });
})();

// ── 5. Bar Kuota ─────────────────────────────────────────
new Chart(document.getElementById('chartKuota'), {
  type:'bar',
  data:{
    labels:['♂ Laki-laki','♀ Perempuan'],
    datasets:[
      {label:'Target Kuota', data:[D.kuota.targetL,D.kuota.targetP], backgroundColor:'#e5e7eb', borderRadius:6},
      {label:'Terpenuhi',    data:[D.kuota.terpL,D.kuota.terpP],     backgroundColor:'#9f1239', borderRadius:6}
    ]
  },
  options:{responsive:true,maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{stepSize:5},grid:{color:'#f9fafb'}}}}
});

// ── 6. Vote Distribution (horizontal bar) ────────────────
(function(){
  const el = document.getElementById('chartVoteDist');
  if (!el) return;
  // Buat data: berapa peserta yang dapat vote_lulus = 1,2,3,...
  const voteMap = {};
  D.skorKNN.forEach(()=>{});

  // Gunakan data langsung dari PHP vote_lulus tiap peserta
  const lulusVotes = <?=json_encode(array_map(function($r){ return (int)$r['vl']; }, $dotUjiLulus))?>;
  const tidakVotes = <?=json_encode(array_map(function($r){ return (int)$r['vl']; }, $dotUjiTidak))?>;

  // Hitung frekuensi vote (0-15)
  const freqL = {}, freqT = {};
  lulusVotes.forEach(v=>{ freqL[v]=(freqL[v]||0)+1; });
  tidakVotes.forEach(v=>{ freqT[v]=(freqT[v]||0)+1; });

  const kMax = <?=$kDefault ?? 3?>;
  const labels = [...Array(kMax+1).keys()].map(i=>`Vote ${i}`);
  const dataL  = labels.map((_,i)=>freqL[i]||0);
  const dataT  = labels.map((_,i)=>freqT[i]||0);

  new Chart(el, {
    type:'bar',
    data:{
      labels,
      datasets:[
        {label:'Peserta Lulus',     data:dataL, backgroundColor:'rgba(16,185,129,.7)', borderRadius:4},
        {label:'Peserta Tidak Lulus',data:dataT, backgroundColor:'rgba(239,68,68,.7)',  borderRadius:4}
      ]
    },
    options:{responsive:true,maintainAspectRatio:false,
      plugins:{legend:{display:false},
        tooltip:{callbacks:{title:ctx=>`Vote Lulus: ${ctx[0].label.replace('Vote ','')}`,
          label:ctx=>`${ctx.dataset.label}: ${ctx.parsed.y} peserta`}}},
      scales:{x:{grid:{display:false},ticks:{font:{size:10}}},
              y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'#f9fafb'}}}}
  });
})();

// ── 7. Distribusi Sekolah (horizontal bar) ───────────────
new Chart(document.getElementById('chartSekolah'), {
  type:'bar',
  data:{
    labels:D.sekolah.labels,
    datasets:[{label:'Peserta',data:D.sekolah.data,
      backgroundColor:['#3b82f6','#9f1239','#f59e0b','#10b981','#8b5cf6','#ec4899'],
      borderRadius:5}]
  },
  options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{x:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'#f9fafb'}},y:{grid:{display:false}}}}
});

// ── 8. Skor KNN (Jarak Euclidean per peserta) ────────────
(function(){
  const el = document.getElementById('chartSkorKNN');
  if (!el) return;
  const lulusData = D.skorKNN.filter(d=>d.status==='Lulus').map((d,i)=>({x:i,y:d.skor}));
  const tidakData = D.skorKNN.filter(d=>d.status==='Tidak Lulus').map((d,i)=>({x:i+lulusData.length,y:d.skor}));
  const allSorted = [...D.skorKNN].sort((a,b)=>a.skor-b.skor);
  new Chart(el, {
    type:'scatter',
    data:{
      datasets:[
        {label:'Lulus', data:allSorted.filter(d=>d.status==='Lulus').map((d,i)=>({x:i,y:d.skor})),
          backgroundColor:'rgba(16,185,129,.8)', borderColor:'#10b981',
          pointRadius:6, pointHoverRadius:8},
        {label:'Tidak Lulus', data:allSorted.filter(d=>d.status==='Tidak Lulus').map((d,i)=>({x:allSorted.filter(d=>d.status==='Lulus').length+i,y:d.skor})),
          backgroundColor:'rgba(239,68,68,.8)', borderColor:'#ef4444',
          pointRadius:6, pointHoverRadius:8},
      ]
    },
    options:{responsive:true,maintainAspectRatio:false,
      plugins:{legend:{position:'top',labels:{font:{size:11},boxWidth:12,padding:14}},
        tooltip:{callbacks:{label:ctx=>`Jarak: ${ctx.parsed.y.toFixed(4)} — ${ctx.dataset.label}`}}},
      scales:{
        x:{display:false},
        y:{title:{display:true,text:'Jarak Euclidean (skor_knn)',font:{size:11,weight:'700'}},
           beginAtZero:true,grid:{color:'#f3f4f6'}}
      }}
  });
})();
}); // end window.load
</script>

<?php
$kDefault = (int)getSetting('k_value', (string)KNN_K);
include __DIR__ . '/../includes/layout_end.php';
?>
