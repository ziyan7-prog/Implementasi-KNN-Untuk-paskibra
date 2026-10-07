<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
requireNotSuperAdmin();

$pdo   = getDB();
$_page = 'laporan';
$pageTitle    = 'Laporan Akhir';
$pageSubtitle = 'Rekap hasil seleksi dan klasifikasi KNN';

$namaKegiatan = getSetting('nama_kegiatan',  'Seleksi Paskibraka');
$tahunSel     = getSetting('tahun_seleksi',   date('Y'));
$instansi     = getSetting('nama_instansi',   'Dinas Pendidikan');
$kota         = getSetting('kota',            'Lebak');
$kepala       = getSetting('nama_kepala',     'Kepala Dinas');
$nip          = getSetting('nip_kepala',      '-');
$tanggalCetak = date('d/m/Y');
$jamCetak     = date('H:i');

$pesertaLulus = $pdo->query("
    SELECT p.nama, p.nomor_peserta, p.jenis_kelamin, p.asal_sekolah,
           hk.status_kelulusan, hk.skor_knn, hk.vote_lulus, hk.vote_tidak_lulus, hk.k_value
    FROM peserta p
    JOIN hasil_knn hk ON hk.id_peserta = p.id
    WHERE hk.status_kelulusan = 'Lulus' AND p.is_active = 1
    ORDER BY p.jenis_kelamin ASC, p.nomor_peserta ASC
")->fetchAll();

$pesertaTidak = $pdo->query("
    SELECT p.nama, p.nomor_peserta, p.jenis_kelamin, p.asal_sekolah,
           hk.status_kelulusan, hk.skor_knn, hk.vote_lulus, hk.vote_tidak_lulus, hk.k_value
    FROM peserta p
    JOIN hasil_knn hk ON hk.id_peserta = p.id
    WHERE hk.status_kelulusan = 'Tidak Lulus' AND p.is_active = 1
    ORDER BY p.jenis_kelamin ASC, p.nomor_peserta ASC
")->fetchAll();

$belumDiproses = $pdo->query("
    SELECT p.nama, p.nomor_peserta, p.jenis_kelamin, p.asal_sekolah
    FROM peserta p
    LEFT JOIN hasil_knn hk ON hk.id_peserta = p.id
    WHERE hk.id_peserta IS NULL AND p.is_active = 1
    ORDER BY p.nama ASC
")->fetchAll();

$lulusL_list = array_values(array_filter($pesertaLulus, fn($r) => $r['jenis_kelamin']==='L'));
$lulusP_list = array_values(array_filter($pesertaLulus, fn($r) => $r['jenis_kelamin']==='P'));
$lulusL = count($lulusL_list);
$lulusP = count($lulusP_list);
$totalLulus = count($pesertaLulus);
$totalTidak = count($pesertaTidak);
$totalL_all = count(array_filter(array_merge($pesertaLulus,$pesertaTidak), fn($r)=>$r['jenis_kelamin']==='L'));
$totalP_all = count(array_filter(array_merge($pesertaLulus,$pesertaTidak), fn($r)=>$r['jenis_kelamin']==='P'));

// JSON untuk dikirim ke popup cetak
$printData = json_encode([
    'namaKegiatan' => $namaKegiatan,
    'tahun'        => $tahunSel,
    'kota'         => $kota,
    'instansi'     => $instansi,
    'kepala'       => $kepala,
    'nip'          => $nip,
    'tanggal'      => $tanggalCetak,
    'jam'          => $jamCetak,
    'lulusL'       => $lulusL,
    'lulusP'       => $lulusP,
    'totalLulus'   => $totalLulus,
    'totalTidak'   => $totalTidak,
    'totalL'       => $totalL_all,
    'totalP'       => $totalP_all,
    'lulusL_list'  => $lulusL_list,
    'lulusP_list'  => $lulusP_list,
], JSON_UNESCAPED_UNICODE);

include __DIR__ . '/../includes/layout.php';
?>

<!-- Page Header -->
<div class="page-header no-print">
  <div>
    <h2 class="page-title"><span class="icon"><i class="fas fa-file-alt"></i></span>Laporan Akhir</h2>
    <p class="page-subtitle">Hasil klasifikasi KNN · <?=htmlspecialchars($namaKegiatan)?> <?=$tahunSel?></p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <button class="btn btn-primary" onclick="cetakLaporan()">
      <i class="fas fa-print"></i> Cetak Laporan
    </button>
    <button class="btn btn-success" onclick="exportLaporanCSV()">
      <i class="fas fa-file-csv"></i> Export CSV
    </button>
  </div>
</div>

<!-- Stat Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:24px;" class="no-print">
  <?php foreach ([
    ['Total Diproses', $totalLulus+$totalTidak, 'fa-users',       '#3b82f6'],
    ['Lulus',          $totalLulus,              'fa-check-circle','#10b981'],
    ['Tidak Lulus',    $totalTidak,              'fa-times-circle','#ef4444'],
    ['Lulus L',        $lulusL,                  'fa-mars',        '#3b82f6'],
    ['Lulus P',        $lulusP,                  'fa-venus',       '#ec4899'],
    ['Belum Diproses', count($belumDiproses),    'fa-clock',       '#f59e0b'],
  ] as [$lbl,$val,$ico,$clr]): ?>
  <div class="stat-card" style="border-top:3px solid <?=$clr?>;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
      <i class="fas <?=$ico?>" style="color:<?=$clr?>;font-size:18px;"></i>
      <span style="font-size:26px;font-weight:800;color:#111827;"><?=$val?></span>
    </div>
    <p style="font-size:11.5px;color:#9ca3af;font-weight:600;margin:0;"><?=$lbl?></p>
  </div>
  <?php endforeach; ?>
</div>

<!-- Tabel Lulus -->
<div class="card no-print" style="padding:24px;margin-bottom:20px;">
  <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;color:#065f46;display:flex;align-items:center;gap:8px;">
    <i class="fas fa-check-circle"></i> Peserta Dinyatakan Lulus (<?=$totalLulus?>)
  </h3>
  <?php if (empty($pesertaLulus)): ?>
  <p style="text-align:center;padding:24px;color:#9ca3af;">Belum ada peserta yang dinyatakan lulus.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data-table" id="tblLulus">
      <thead><tr><th>#</th><th>No. Peserta</th><th>Nama</th><th>JK</th><th>Sekolah</th><th>Vote L</th><th>Jarak</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($pesertaLulus as $i => $p): ?>
        <tr>
          <td style="color:#9ca3af;font-size:12px;"><?=$i+1?></td>
          <td style="font-family:monospace;font-size:12px;"><?=htmlspecialchars($p['nomor_peserta']??'—')?></td>
          <td style="font-weight:600;"><?=htmlspecialchars($p['nama'])?></td>
          <td><?=jkLabel($p['jenis_kelamin']??'')?></td>
          <td style="color:#6b7280;font-size:12.5px;"><?=htmlspecialchars($p['asal_sekolah']??'—')?></td>
          <td style="text-align:center;font-weight:700;color:#059669;"><?=$p['vote_lulus']??'—'?></td>
          <td style="font-family:monospace;font-size:12px;color:#7c3aed;"><?=number_format($p['skor_knn'],4)?></td>
          <td><span class="badge badge-success"><i class="fas fa-check"></i> Lulus</span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if (!empty($pesertaTidak)): ?>
<div class="card no-print" style="padding:24px;margin-bottom:20px;">
  <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;color:#991b1b;display:flex;align-items:center;gap:8px;">
    <i class="fas fa-times-circle"></i> Peserta Tidak Lulus (<?=$totalTidak?>)
  </h3>
  <div class="table-wrap">
    <table class="data-table" id="tblTidak">
      <thead><tr><th>#</th><th>No. Peserta</th><th>Nama</th><th>JK</th><th>Sekolah</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($pesertaTidak as $i => $p): ?>
        <tr>
          <td style="color:#9ca3af;font-size:12px;"><?=$i+1?></td>
          <td style="font-family:monospace;font-size:12px;"><?=htmlspecialchars($p['nomor_peserta']??'—')?></td>
          <td style="font-weight:600;"><?=htmlspecialchars($p['nama'])?></td>
          <td><?=jkLabel($p['jenis_kelamin']??'')?></td>
          <td style="color:#6b7280;font-size:12.5px;"><?=htmlspecialchars($p['asal_sekolah']??'—')?></td>
          <td><span class="badge badge-danger"><i class="fas fa-times"></i> Tidak Lulus</span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Data JSON untuk JS -->
<script>
const PRINT_DATA = <?= $printData ?>;

function cetakLaporan() {
  const d = PRINT_DATA;

  // Buat baris tabel lulus L
  let rowsL = '';
  d.lulusL_list.forEach((p, i) => {
    rowsL += `<tr>
      <td style="text-align:center;">${i+1}</td>
      <td>${p.nomor_peserta||'—'}</td>
      <td style="font-weight:bold;">${p.nama}</td>
      <td>${p.asal_sekolah||'—'}</td>
      <td style="text-align:center;color:#065f46;font-weight:bold;">✓ LULUS</td>
    </tr>`;
  });

  let rowsP = '';
  d.lulusP_list.forEach((p, i) => {
    rowsP += `<tr>
      <td style="text-align:center;">${i+1}</td>
      <td>${p.nomor_peserta||'—'}</td>
      <td style="font-weight:bold;">${p.nama}</td>
      <td>${p.asal_sekolah||'—'}</td>
      <td style="text-align:center;color:#065f46;font-weight:bold;">✓ LULUS</td>
    </tr>`;
  });

  const tabelL = d.lulusL_list.length > 0 ? `
    <p class="section-label">LAKI-LAKI (${d.lulusL} peserta)</p>
    <table>
      <thead><tr><th>No</th><th>No. Peserta</th><th>Nama Peserta</th><th>Asal Sekolah</th><th>Keterangan</th></tr></thead>
      <tbody>${rowsL}</tbody>
    </table>` : '';

  const tabelP = d.lulusP_list.length > 0 ? `
    <p class="section-label">PEREMPUAN (${d.lulusP} peserta)</p>
    <table>
      <thead><tr><th>No</th><th>No. Peserta</th><th>Nama Peserta</th><th>Asal Sekolah</th><th>Keterangan</th></tr></thead>
      <tbody>${rowsP}</tbody>
    </table>` : '';

  const noData = d.totalLulus === 0
    ? '<p style="text-align:center;color:#777;font-style:italic;padding:20pt 0;">Belum ada peserta yang dinyatakan lulus.</p>'
    : '';

  const ttd = `
    <div class="ttd-area">
      <div class="ttd-box">
        <p>.............................., ${d.tanggal}</p>
        <p style="margin:6pt 0 2pt;">Panitia Seleksi,</p>
        <div class="ttd-line">
          ${d.kepala}
          ${d.nip !== '-' ? `<div class="ttd-nip">NIP. ${d.nip}</div>` : ''}
        </div>
      </div>
    </div>`;

  const html = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Akhir Seleksi Paskibraka</title>
<style>
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:Arial,sans-serif; font-size:10pt; color:#000; background:#fff; padding:1.5cm; }
  .header { text-align:center; margin-bottom:12pt; }
  .header .title { font-size:13pt; font-weight:bold; text-transform:uppercase; letter-spacing:1px; margin-bottom:3pt; }
  .header .sub   { font-size:11pt; font-weight:bold; text-transform:uppercase; margin-bottom:3pt; }
  .header .meta  { font-size:8.5pt; color:#444; }
  .divider { border:none; border-top:2.5px solid #7f0000; margin:8pt 0 10pt; }
  .summary { display:flex; gap:24pt; font-size:9pt; font-weight:bold; margin-bottom:12pt; }
  .section-label { font-size:9pt; font-weight:bold; color:#555; margin:10pt 0 5pt; letter-spacing:0.5px; }
  table { width:100%; border-collapse:collapse; margin-bottom:10pt; font-size:9pt; }
  thead tr { background:#8b0000; color:#fff; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  thead th { padding:5pt 7pt; text-align:left; font-weight:bold; border:1px solid #6b0000; white-space:nowrap; }
  tbody td { padding:4.5pt 7pt; border:1px solid #ddd; vertical-align:middle; }
  tbody tr:nth-child(even) td { background:#fef9f9; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  .ttd-area { margin-top:28pt; display:flex; justify-content:flex-end; }
  .ttd-box  { text-align:center; min-width:180pt; font-size:9.5pt; }
  .ttd-line { margin-top:44pt; border-top:1px solid #000; padding-top:3pt; font-weight:bold; font-size:9.5pt; }
  .ttd-nip  { font-weight:normal; font-size:8.5pt; margin-top:2pt; }
  .footer   { margin-top:16pt; text-align:center; font-size:7.5pt; color:#777; border-top:1px solid #e5e7eb; padding-top:6pt; }
  @media print { @page { margin:1.5cm; size:A4 portrait; } body { padding:0; } }
</style>
</head>
<body>
  <div class="header">
    <p class="title">Daftar Peserta yang Dinyatakan Lulus</p>
    <p class="sub">Seleksi ${d.namaKegiatan} ${d.kota} Tahun ${d.tahun}</p>
    <p class="meta">Dicetak: ${d.tanggal} ${d.jam} &nbsp;|&nbsp; Total Lulus: ${d.totalLulus} peserta (${d.lulusL} Laki-laki dan ${d.lulusP} Perempuan)</p>
  </div>
  <hr class="divider">
  <div class="summary">
    <span>Total Lulus: <span style="color:#065f46">${d.totalLulus} peserta</span></span>
    <span>Laki-laki: <span style="color:#1d4ed8">${d.lulusL} / ${d.totalL}</span></span>
    <span>Perempuan: <span style="color:#be185d">${d.lulusP} / ${d.totalP}</span></span>
  </div>
  ${tabelL}
  ${tabelP}
  ${noData}
  ${ttd}
  <div class="footer">
    © ${d.tahun} SiPaskibraka · Sistem Klasifikasi Kelayakan Seleksi Paskibraka
  </div>
<script>window.onload=function(){window.print();window.onafterprint=function(){window.close();}}<\/script>
</body>
</html>`;

  const popup = window.open('', '_blank', 'width=900,height=700,scrollbars=yes');
  popup.document.write(html);
  popup.document.close();
}

function exportLaporanCSV() {
  const rows = [['No','No Peserta','Nama','JK','Sekolah','Status']];
  <?php foreach ($pesertaLulus as $i => $p): ?>
  rows.push([<?=$i+1?>,'<?=addslashes($p['nomor_peserta']??'—')?>','<?=addslashes($p['nama'])?>','<?=$p['jenis_kelamin']?>','<?=addslashes($p['asal_sekolah']??'—')?>','Lulus']);
  <?php endforeach; ?>
  <?php foreach ($pesertaTidak as $i => $p): ?>
  rows.push([<?=$totalLulus+$i+1?>,'<?=addslashes($p['nomor_peserta']??'—')?>','<?=addslashes($p['nama'])?>','<?=$p['jenis_kelamin']?>','<?=addslashes($p['asal_sekolah']??'—')?>','Tidak Lulus']);
  <?php endforeach; ?>
  const bom = '\uFEFF';
  const csv = bom + rows.map(r => r.map(v=>`"${String(v).replace(/"/g,'""')}"`).join(',')).join('\r\n');
  const blob = new Blob([csv],{type:'text/csv;charset=utf-8;'});
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href=url; a.download='laporan_kelulusan.csv'; a.click();
  URL.revokeObjectURL(url);
}
</script>

<?php include __DIR__ . '/../includes/layout_end.php'; ?>
