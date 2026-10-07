<?php
/**
 * detail_knn.php — Tampilkan tahap perhitungan KNN secara detail untuk satu peserta
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/knn.php';
requireAdmin();
requireNotSuperAdmin();

$pdo = getDB();
$idPeserta = (int)($_GET['id'] ?? 0);
$k         = max(1, min(15, (int)($_GET['k'] ?? (int)getSetting('k_value', (string)KNN_K))));

if ($idPeserta <= 0) {
    flash('ID peserta tidak valid.', 'error');
    header('Location: proses_knn.php'); exit;
}

// Ambil data peserta
$sPeserta = $pdo->prepare("SELECT * FROM peserta WHERE id=? AND is_active=1 LIMIT 1");
$sPeserta->execute([$idPeserta]);
$peserta = $sPeserta->fetch();
if (!$peserta) {
    flash('Peserta tidak ditemukan.', 'error');
    header('Location: proses_knn.php'); exit;
}

// Ambil hasil tersimpan (jika ada)
$sHasil = $pdo->prepare("SELECT * FROM hasil_knn WHERE id_peserta=? LIMIT 1");
$sHasil->execute([$idPeserta]);
$hasilDb = $sHasil->fetch();

// Jalankan ulang KNN untuk menampilkan langkah-langkah
$fields  = getFeatureFields();
$dataUji = array_intersect_key($peserta, array_flip($fields));
$nilaiLengkap = !in_array(null, $dataUji, true) && !in_array('', $dataUji, true);

$knnResult = null;
$knnError  = '';
if ($nilaiLengkap) {
    $knnResult = hitungKNNDariData($dataUji, $k);
    if ($knnResult['error']) { $knnError = $knnResult['message']; $knnResult = null; }
}

$_page        = 'proses_knn';
$pageTitle    = 'Detail KNN';
$pageSubtitle = 'Tahap perhitungan KNN — Detail langkah algoritma K-Nearest Neighbor';

include __DIR__ . '/../includes/layout.php';

$fieldLabel = [
    'nilai_pip'   => 'Nilai PIP',
    'nilai_tiu'   => 'Nilai TIU',
    'tinggi_badan'=> 'Tinggi Badan',
    'berat_badan' => 'Berat Badan',
    'kesehatan'   => 'Kesehatan',
    'push_up'     => 'Push Up',
    'sit_up'      => 'Sit Up',
    'back_up'     => 'Back Up',
    'lari'        => 'Lari',
    'pbb'         => 'PBB',
    'kepribadian' => 'Kepribadian',
];
?>

<div class="page-header">
  <div>
    <h2 class="page-title"><span class="icon"><i class="fas fa-microscope"></i></span>Detail Perhitungan KNN</h2>
    <p class="page-subtitle">Tahap perhitungan algoritma K-Nearest Neighbor</p>
  </div>
  <div style="display:flex;gap:8px;">
    <!-- Ganti K -->
    <form method="GET" style="display:flex;gap:8px;align-items:center;">
      <input type="hidden" name="id" value="<?= $idPeserta ?>">
      <select name="k" class="form-control form-select" style="width:100px;" onchange="this.form.submit()">
        <?php for ($ki=1;$ki<=15;$ki+=2): ?>
        <option value="<?=$ki?>" <?=$ki===$k?'selected':''?>>K = <?=$ki?></option>
        <?php endfor; ?>
      </select>
    </form>
    <a href="proses_knn.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
</div>

<!-- Info Peserta Card -->
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px 20px;margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;box-shadow:0 1px 4px rgba(0,0,0,.05);">
  <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#9f1239,#7f1d1d);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 3px 10px rgba(159,18,57,.25);">
    <i class="fas fa-user" style="color:#fff;font-size:16px;"></i>
  </div>
  <div style="flex:1;min-width:0;">
    <div style="font-size:15px;font-weight:800;color:#111827;line-height:1.2;"><?= htmlspecialchars(ucwords(strtolower($peserta['nama']))) ?></div>
    <div style="font-size:12px;color:#6b7280;margin-top:3px;">
      <?= htmlspecialchars($peserta['nomor_peserta'] ?? '-') ?>
      &nbsp;·&nbsp;
      <?= $peserta['jenis_kelamin'] === 'L' ? '<i class="fas fa-mars" style="color:#3b82f6;"></i> Laki-laki' : '<i class="fas fa-venus" style="color:#ec4899;"></i> Perempuan' ?>
      &nbsp;·&nbsp;
      <?= htmlspecialchars($peserta['asal_sekolah'] ?? '-') ?>
    </div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:8px 14px;text-align:center;">
      <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Nilai K</div>
      <div style="font-size:16px;font-weight:800;color:#059669;">K = <?= $k ?></div>
    </div>
    <?php if (!$knnError && $nilaiLengkap): ?>
    <div style="background:<?= ($hasilKNN['status_kelulusan']??'')==='Lulus' ? '#f0fdf4' : '#fef2f2' ?>;border:1px solid <?= ($hasilKNN['status_kelulusan']??'')==='Lulus' ? '#bbf7d0' : '#fecaca' ?>;border-radius:10px;padding:8px 14px;text-align:center;">
      <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Hasil KNN</div>
      <div style="font-size:15px;font-weight:800;color:<?= ($hasilKNN['status_kelulusan']??'')==='Lulus' ? '#059669' : '#dc2626' ?>;">
        <i class="fas fa-<?= ($hasilKNN['status_kelulusan']??'')==='Lulus' ? 'check-circle' : 'times-circle' ?>"></i>
        <?= htmlspecialchars($hasilKNN['status_kelulusan'] ?? 'Belum') ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($knnError): ?>
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($knnError) ?></div>
<?php elseif (!$nilaiLengkap): ?>
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Nilai peserta belum lengkap. <a href="nilai.php?peserta=<?= $idPeserta ?>">Input nilai sekarang →</a></div>
<?php else: ?>

<!-- ══ LANGKAH 1: Data Peserta ══ -->
<div class="card" style="padding:22px;margin-bottom:20px;">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
    <div style="width:32px;height:32px;border-radius:50%;background:#9f1239;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;">1</div>
    <div>
      <h3 style="font-size:15px;font-weight:700;margin:0;color:#111827;">Data Nilai Peserta (Data Uji)</h3>
      <p style="font-size:12px;color:#9ca3af;margin:0;">Nilai asli sebelum normalisasi</p>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">
    <?php foreach ($fields as $i => $f): ?>
    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;">
      <p style="font-size:11px;color:#9ca3af;font-weight:600;margin:0 0 4px;"><?= $fieldLabel[$f] ?></p>
      <p style="font-size:20px;font-weight:800;color:#111827;margin:0;"><?= number_format((float)$dataUji[$f], 1) ?></p>
      <p style="font-size:11px;color:#7c3aed;margin:4px 0 0;">Norm: <strong><?= number_format($knnResult['uji_normalized'][$i], 4) ?></strong></p>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ══ LANGKAH 2: Normalisasi Min-Max ══ -->
<div class="card" style="padding:22px;margin-bottom:20px;">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
    <div style="width:32px;height:32px;border-radius:50%;background:#3b82f6;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;">2</div>
    <div>
      <h3 style="font-size:15px;font-weight:700;margin:0;color:#111827;">Normalisasi Min-Max</h3>
      <p style="font-size:12px;color:#9ca3af;margin:0;">Formula: x' = (x − min) / (max − min)</p>
    </div>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Fitur</th>
          <th style="text-align:center;">Nilai Asli</th>
          <th style="text-align:center;">Min</th>
          <th style="text-align:center;">Max</th>
          <th style="text-align:center;">Hasil Normalisasi</th>
          <th>Cara Hitung</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fields as $i => $f):
          $xAsli = (float)$dataUji[$f];
          $min   = $knnResult['minmax']['min'][$f];
          $max   = $knnResult['minmax']['max'][$f];
          $norm  = $knnResult['uji_normalized'][$i];
        ?>
        <tr>
          <td style="font-weight:600;"><?= $fieldLabel[$f] ?></td>
          <td style="text-align:center;font-weight:700;color:#111827;"><?= number_format($xAsli,1) ?></td>
          <td style="text-align:center;color:#6b7280;"><?= number_format($min,1) ?></td>
          <td style="text-align:center;color:#6b7280;"><?= number_format($max,1) ?></td>
          <td style="text-align:center;font-weight:700;color:#7c3aed;"><?= number_format($norm,6) ?></td>
          <td style="font-family:monospace;font-size:11.5px;color:#6b7280;">
            (<?= number_format($xAsli,1) ?> − <?= number_format($min,1) ?>) / (<?= number_format($max,1) ?> − <?= number_format($min,1) ?>) = <strong><?= number_format($norm,4) ?></strong>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ══ LANGKAH 3: Perhitungan Jarak Euclidean ══ -->
<?php
$dataLatih = getDataLatih();
$minMax    = $knnResult['minmax'];
$ujiNorm   = $knnResult['uji_normalized'];
$allJarak  = [];
foreach ($dataLatih as $latih) {
    $latihNorm = [];
    foreach ($fields as $f) {
        $latihNorm[] = minMaxNormalize((float)$latih[$f], $minMax['min'][$f], $minMax['max'][$f]);
    }
    $komponen = []; $sum = 0.0;
    foreach ($ujiNorm as $idx => $uv) {
        $diff = $uv - $latihNorm[$idx]; $sq = $diff * $diff;
        $komponen[] = ['diff'=>round($uv-$latihNorm[$idx],6),'sq'=>round($sq,6),'uji'=>round($uv,6),'latih'=>round($latihNorm[$idx],6)];
        $sum += $sq;
    }
    $allJarak[] = ['nama'=>$latih['nama_referensi']??'Data-'.$latih['id'],'label'=>$latih['label'],
                   'jarak'=>sqrt($sum),'sum_sq'=>round($sum,6),'komponen'=>$komponen];
}
usort($allJarak, fn($a,$b) => $a['jarak'] <=> $b['jarak']);
?>

<div class="card" style="padding:22px;margin-bottom:20px;">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
    <div style="width:32px;height:32px;border-radius:50%;background:#f59e0b;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;">3</div>
    <div>
      <h3 style="font-size:15px;font-weight:700;margin:0;color:#111827;">Perhitungan Jarak Euclidean ke Semua Data Latih</h3>
      <p style="font-size:12px;color:#9ca3af;margin:0;">Formula: d(x,y) = √Σ(x_i − y_i)²&nbsp;|&nbsp;Klik tombol <em>Rumus</em> untuk lihat detail per data latih</p>
    </div>
  </div>

  <!-- Rumus generik di atas tabel -->
  <div style="background:#ede9fe;border:1px solid #c4b5fd;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-family:monospace;font-size:12px;color:#4c1d95;">
    <strong>d(uji, latih)</strong> = √ [ (pip_u−pip_l)² + (tiu_u−tiu_l)² + (tb_u−tb_l)² + (bb_u−bb_l)² + (kes_u−kes_l)² + (pu_u−pu_l)² + (su_u−su_l)² + (bu_u−bu_l)² + (lari_u−lari_l)² + (pbb_u−pbb_l)² + (kep_u−kep_l)² ]
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th style="text-align:center;">Rank</th>
          <th>Data Latih</th>
          <th style="text-align:center;">Label</th>
          <th style="text-align:center;color:#7c3aed;">Jarak Euclidean</th>
          <th style="text-align:center;">Detail Rumus</th>
          <th style="text-align:center;">Tetangga</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allJarak as $rank => $row):
            $isNeighbor = $rank < $k; ?>
        <tr style="<?= $isNeighbor ? 'background:#f0fdf4;' : '' ?>">
          <td style="text-align:center;font-weight:700;color:<?= $isNeighbor ? '#065f46' : '#9ca3af' ?>;"><?= $rank+1 ?></td>
          <td style="font-weight:<?= $isNeighbor ? '700' : '400' ?>;"><?= htmlspecialchars($row['nama']) ?></td>
          <td style="text-align:center;">
            <?= $row['label']==='Lulus'
              ? '<span class="badge badge-success">Lulus</span>'
              : '<span class="badge badge-danger">Tidak Lulus</span>' ?>
          </td>
          <td style="text-align:center;font-family:monospace;font-weight:<?= $isNeighbor ? '700' : '400' ?>;color:#7c3aed;">
            <?= number_format($row['jarak'], 6) ?>
          </td>
          <td style="text-align:center;">
            <button type="button" onclick="toggleDetail('euc-<?= $rank ?>')"
              style="background:<?= $isNeighbor?'#ede9fe':'#f9fafb' ?>;border:1px solid <?= $isNeighbor?'#c4b5fd':'#e5e7eb' ?>;border-radius:6px;padding:3px 10px;font-size:11.5px;cursor:pointer;color:<?= $isNeighbor?'#5b21b6':'#6b7280' ?>;">
              <i class="fas fa-calculator"></i> Rumus
            </button>
          </td>
          <td style="text-align:center;">
            <?= $isNeighbor
              ? '<span class="badge badge-success"><i class="fas fa-check"></i> K-'.($rank+1).'</span>'
              : '<span style="color:#d1d5db;font-size:12px;">—</span>' ?>
          </td>
        </tr>
        <!-- Detail rumus Euclidean expandable -->
        <tr id="euc-<?= $rank ?>" style="display:none;">
          <td colspan="6" style="padding:2px 10px 10px;border-top:none;background:<?= $isNeighbor?'#f5f3ff':'#fafafa' ?>;">
            <div style="border:1px solid <?= $isNeighbor?'#c4b5fd':'#e5e7eb' ?>;border-radius:10px;padding:14px 16px;">

              <!-- Header rumus dengan nilai peserta vs data latih ini -->
              <p style="font-size:11.5px;font-weight:700;color:#4338ca;margin:0 0 12px;word-break:break-all;">
                <i class="fas fa-square-root-alt"></i>&nbsp;
                d("<?= htmlspecialchars($peserta['nama']) ?>", "<?= htmlspecialchars($row['nama']) ?>") = √[
                <?php $sqStrs = [];
                foreach ($row['komponen'] as $idx => $kom) {
                    $lbl = explode(' ', $fieldLabel[$fields[$idx]])[0];
                    $sqStrs[] = "({$kom['uji']}−{$kom['latih']})²";
                }
                echo implode(' + ', $sqStrs); ?> ]
              </p>

              <!-- Grid komponen per fitur -->
              <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;margin-bottom:12px;">
                <?php foreach ($fields as $idx => $f):
                  $kom = $row['komponen'][$idx]; ?>
                <div style="background:#fff;border:1px solid <?= $isNeighbor?'#c4b5fd':'#e5e7eb' ?>;border-radius:8px;padding:8px 10px;font-size:11px;">
                  <p style="font-weight:700;color:#374151;margin:0 0 5px;font-size:11.5px;"><?= $fieldLabel[$f] ?></p>
                  <table style="width:100%;font-family:monospace;font-size:10.5px;border:none;">
                    <tr><td style="color:#6b7280;border:none;padding:1px 0;">x_uji</td>  <td style="color:#059669;font-weight:700;border:none;padding:1px 0;">= <?= $kom['uji'] ?></td></tr>
                    <tr><td style="color:#6b7280;border:none;padding:1px 0;">x_latih</td><td style="color:#dc2626;font-weight:700;border:none;padding:1px 0;">= <?= $kom['latih'] ?></td></tr>
                    <tr style="border-top:1px dashed #e5e7eb;"><td style="color:#6b7280;border:none;padding:2px 0;">selisih</td><td style="color:#0369a1;font-weight:700;border:none;padding:2px 0;">= <?= $kom['diff'] ?></td></tr>
                    <tr><td style="color:#6b7280;border:none;padding:1px 0;">(selisih)²</td><td style="color:#7c3aed;font-weight:700;border:none;padding:1px 0;">= <?= $kom['sq'] ?></td></tr>
                  </table>
                </div>
                <?php endforeach; ?>
              </div>

              <!-- Baris total -->
              <div style="background:<?= $isNeighbor?'#ede9fe':'#f3f4f6' ?>;border:1px solid <?= $isNeighbor?'#c4b5fd':'#d1d5db' ?>;border-radius:8px;padding:10px 14px;font-family:monospace;font-size:12px;color:#1f2937;">
                <?php
                  $sqParts = array_column($row['komponen'], 'sq');
                  $sumStr  = implode(' + ', $sqParts);
                ?>
                <div style="color:#6b7280;margin-bottom:4px;">Σ(selisih²) = <?= $sumStr ?></div>
                <div style="color:#374151;margin-bottom:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;= <strong><?= $row['sum_sq'] ?></strong></div>
                <div style="font-size:13px;">d = √<?= $row['sum_sq'] ?> = <strong style="color:#7c3aed;font-size:14px;"><?= number_format($row['jarak'],6) ?></strong>
                  <?= $isNeighbor ? '<span style="margin-left:10px;" class="badge badge-success"><i class="fas fa-star"></i> Masuk K-'.($rank+1).'</span>' : '' ?>
                </div>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function toggleDetail(id) {
  const row = document.getElementById(id);
  if (!row) return;
  const open = row.style.display === 'none';
  row.style.display = open ? 'table-row' : 'none';
  // scroll ke detail jika dibuka
  if (open) setTimeout(()=> row.scrollIntoView({behavior:'smooth',block:'nearest'}), 50);
}
</script>

<!-- ══ LANGKAH 4: K Tetangga Terdekat ══ -->
<div class="card" style="padding:22px;margin-bottom:20px;">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
    <div style="width:32px;height:32px;border-radius:50%;background:#8b5cf6;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;">4</div>
    <div>
      <h3 style="font-size:15px;font-weight:700;margin:0;color:#111827;"><?= $k ?> Tetangga Terdekat (K = <?= $k ?>)</h3>
      <p style="font-size:12px;color:#9ca3af;margin:0;">Hanya <?= $k ?> data latih dengan jarak terkecil yang digunakan untuk voting</p>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:18px;">
    <?php foreach ($knnResult['k_neighbors'] as $n): ?>
    <div style="border:2px solid <?= $n['label']==='Lulus' ? '#10b981' : '#ef4444' ?>;border-radius:12px;padding:14px 16px;background:<?= $n['label']==='Lulus' ? '#f0fdf4' : '#fef2f2' ?>;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
        <span style="background:<?= $n['label']==='Lulus' ? '#10b981' : '#ef4444' ?>;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;">K-<?= $n['rank'] ?></span>
        <?= $n['label']==='Lulus'
          ? '<span class="badge badge-success">Lulus</span>'
          : '<span class="badge badge-danger">Tidak Lulus</span>' ?>
      </div>
      <p style="font-weight:700;font-size:13px;margin:0 0 4px;color:#111827;"><?= htmlspecialchars($n['nama_referensi']) ?></p>
      <p style="font-family:monospace;font-size:12px;color:#7c3aed;margin:0;">d = <?= number_format($n['jarak'],6) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ══ LANGKAH 5: Majority Voting & Kesimpulan ══ -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

  <!-- Voting -->
  <div class="card" style="padding:22px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
      <div style="width:32px;height:32px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;">5</div>
      <div>
        <h3 style="font-size:15px;font-weight:700;margin:0;color:#111827;">Majority Voting</h3>
        <p style="font-size:12px;color:#9ca3af;margin:0;">Kelas terbanyak dari <?= $k ?> tetangga</p>
      </div>
    </div>
    <?php
      $vL = $knnResult['votes']['Lulus'];
      $vT = $knnResult['votes']['Tidak Lulus'];
      $pctL = round($vL / $k * 100);
      $pctT = 100 - $pctL;
    ?>
    <div style="margin-bottom:16px;">
      <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
        <span style="font-weight:700;color:#059669;font-size:13px;"><i class="fas fa-check-circle"></i> Lulus</span>
        <span style="font-weight:800;font-size:18px;color:#059669;"><?= $vL ?> suara</span>
      </div>
      <div style="height:28px;background:#f3f4f6;border-radius:8px;overflow:hidden;">
        <div style="height:100%;width:<?= $pctL ?>%;background:#10b981;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;">
          <?= $pctL > 15 ? $pctL.'%' : '' ?>
        </div>
      </div>
    </div>
    <div style="margin-bottom:20px;">
      <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
        <span style="font-weight:700;color:#dc2626;font-size:13px;"><i class="fas fa-times-circle"></i> Tidak Lulus</span>
        <span style="font-weight:800;font-size:18px;color:#dc2626;"><?= $vT ?> suara</span>
      </div>
      <div style="height:28px;background:#f3f4f6;border-radius:8px;overflow:hidden;">
        <div style="height:100%;width:<?= $pctT ?>%;background:#ef4444;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;">
          <?= $pctT > 15 ? $pctT.'%' : '' ?>
        </div>
      </div>
    </div>
    <div style="padding:12px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;font-size:12.5px;color:#6b7280;line-height:1.6;">
      <?php foreach ($knnResult['k_neighbors'] as $n): ?>
      <div>K-<?= $n['rank'] ?> → <strong><?= htmlspecialchars($n['nama_referensi']) ?></strong>: 
        <span style="color:<?= $n['label']==='Lulus'?'#059669':'#dc2626' ?>;font-weight:700;"><?= $n['label'] ?></span>
        <span style="color:#9ca3af;font-size:11px;">(d=<?= number_format($n['jarak'],4) ?>)</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Kesimpulan -->
  <div class="card" style="padding:22px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
      <div style="width:32px;height:32px;border-radius:50%;background:<?= $knnResult['label']==='Lulus'?'#10b981':'#ef4444' ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;">6</div>
      <div>
        <h3 style="font-size:15px;font-weight:700;margin:0;color:#111827;">Kesimpulan & Keputusan KNN</h3>
        <p style="font-size:12px;color:#9ca3af;margin:0;">Kelas mayoritas = hasil klasifikasi</p>
      </div>
    </div>

    <!-- Hasil besar -->
    <div style="text-align:center;padding:28px 20px;border-radius:14px;background:<?= $knnResult['label']==='Lulus' ? 'linear-gradient(135deg,#d1fae5,#a7f3d0)' : 'linear-gradient(135deg,#fee2e2,#fecaca)' ?>;border:2px solid <?= $knnResult['label']==='Lulus'?'#10b981':'#ef4444' ?>;margin-bottom:16px;">
      <i class="fas <?= $knnResult['label']==='Lulus' ? 'fa-check-circle' : 'fa-times-circle' ?>"
         style="font-size:40px;color:<?= $knnResult['label']==='Lulus'?'#059669':'#dc2626' ?>;margin-bottom:12px;display:block;"></i>
      <p style="font-size:26px;font-weight:900;margin:0;color:<?= $knnResult['label']==='Lulus'?'#065f46':'#991b1b' ?>;">
        <?= $knnResult['label'] ?>
      </p>
      <p style="font-size:13px;color:<?= $knnResult['label']==='Lulus'?'#059669':'#b91c1c' ?>;margin:6px 0 0;">
        <?= $vL ?> vs <?= $vT ?> suara dari <?= $k ?> tetangga terdekat
      </p>
    </div>

    <!-- Info detail -->
    <div style="font-size:12.5px;color:#374151;line-height:2;">
      <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f3f4f6;padding-bottom:4px;margin-bottom:4px;">
        <span style="color:#9ca3af;">Peserta</span>
        <span style="font-weight:700;"><?= htmlspecialchars($peserta['nama']) ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f3f4f6;padding-bottom:4px;margin-bottom:4px;">
        <span style="color:#9ca3af;">Nilai K</span>
        <span style="font-weight:700;">K = <?= $k ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f3f4f6;padding-bottom:4px;margin-bottom:4px;">
        <span style="color:#9ca3af;">Jarak Tetangga #1</span>
        <span style="font-family:monospace;font-weight:700;color:#7c3aed;"><?= number_format($knnResult['jarak_terdekat'],6) ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f3f4f6;padding-bottom:4px;margin-bottom:4px;">
        <span style="color:#9ca3af;">Vote Lulus</span>
        <span style="font-weight:700;color:#059669;"><?= $vL ?> / <?= $k ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;">
        <span style="color:#9ca3af;">Status Tersimpan</span>
        <span><?= $hasilDb ? statusBadge($hasilDb['status_kelulusan']) : '<span style="color:#9ca3af;">Belum disimpan</span>' ?></span>
      </div>
    </div>

    <!-- Simpan -->
    <div style="margin-top:16px;display:flex;gap:8px;">
      <form method="POST" action="proses_knn.php">
        <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
        <input type="hidden" name="action" value="proses_satu">
        <input type="hidden" name="id_peserta" value="<?= $idPeserta ?>">
        <input type="hidden" name="k_value" value="<?= $k ?>">
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save"></i> Simpan Hasil KNN
        </button>
      </form>
      <a href="proses_knn.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
      </a>
    </div>
  </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../includes/layout_end.php'; ?>
