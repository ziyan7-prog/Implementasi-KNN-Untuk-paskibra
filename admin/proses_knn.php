<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/knn.php';
requireAdmin();
requireNotSuperAdmin();

$pdo     = getDB();
$_page   = 'proses_knn';
$pageTitle    = 'Proses KNN';
$pageSubtitle = 'Klasifikasi kelayakan peserta menggunakan K-Nearest Neighbor';
$csrf    = generateCSRF();
$adminId = $_SESSION['admin_id'] ?? 0;
$kDefault = (int)getSetting('k_value', (string)KNN_K);

// ── POST HANDLER ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        flash('Token tidak valid.', 'error'); header('Location: proses_knn.php'); exit;
    }
    $action = $_POST['action'] ?? '';
    $k = max(1, min(15, (int)($_POST['k_value'] ?? $kDefault)));

    if ($action === 'proses_satu') {
        $id  = (int)($_POST['id_peserta'] ?? 0);
        if ($id <= 0) { flash('Pilih peserta terlebih dahulu.', 'error'); header('Location: proses_knn.php'); exit; }
        $res = prosesKNNPeserta($id, $k);
        if ($res['error']) { flash($res['message'], 'error'); }
        else {
            simpanHasilKNN($id, $res, $k, $adminId);
            $s = $pdo->prepare("SELECT nama FROM peserta WHERE id=? LIMIT 1");
            $s->execute([$id]); $nama = $s->fetchColumn();
            catatLog('KNN_SATU', "Peserta=$nama K=$k Hasil={$res['label']}");
            flash("KNN selesai — <strong>$nama</strong>: <strong>{$res['label']}</strong>");
        }
        header('Location: proses_knn.php'); exit;

    } elseif ($action === 'proses_semua') {
        $pesertaList = $pdo->query("SELECT id,nama FROM peserta WHERE is_active=1
            AND nilai_pip IS NOT NULL AND nilai_tiu IS NOT NULL
            AND tinggi_badan IS NOT NULL AND push_up IS NOT NULL")->fetchAll();
        $ok = 0; $fail = 0;
        foreach ($pesertaList as $p) {
            $res = prosesKNNPeserta((int)$p['id'], $k);
            if (!$res['error']) { simpanHasilKNN((int)$p['id'], $res, $k, $adminId); $ok++; }
            else $fail++;
        }
        catatLog('KNN_SEMUA', "K=$k OK=$ok Fail=$fail");
        flash("Proses KNN selesai: <strong>$ok</strong> berhasil" . ($fail ? ", <strong>$fail</strong> gagal (nilai belum lengkap)" : '') . ".");
        header('Location: proses_knn.php'); exit;

    } elseif ($action === 'terapkan_kuota') {
        $kuotaL = (int)($_POST['kuota_l'] ?? (int)getSetting('kuota_lulus_laki','35'));
        $kuotaP = (int)($_POST['kuota_p'] ?? (int)getSetting('kuota_lulus_perempuan','35'));
        $hasil  = terapkanKuota($kuotaL, $kuotaP, $adminId);
        if ($hasil['ok']) {
            setSetting('kuota_lulus_laki',       (string)$kuotaL);
            setSetting('kuota_lulus_perempuan',   (string)$kuotaP);
            catatLog('TERAPKAN_KUOTA', "L=$kuotaL P=$kuotaP LulusL={$hasil['lulus_l']} LulusP={$hasil['lulus_p']}");
            flash("Kuota diterapkan — Lulus: <strong>{$hasil['total_lulus']}</strong> (♂{$hasil['lulus_l']} ♀{$hasil['lulus_p']})");
        } else { flash($hasil['msg'], 'error'); }
        header('Location: proses_knn.php'); exit;

    } elseif ($action === 'reset_knn') {
        $pdo->exec("DELETE FROM hasil_knn");
        catatLog('RESET_KNN', 'Semua hasil KNN dihapus');
        flash('Semua hasil KNN berhasil direset.', 'warning');
        header('Location: proses_knn.php'); exit;

    }
}

// ── GET DATA ──────────────────────────────────────────────
try {
    $pesertaAll = $pdo->query("
        SELECT p.id, p.nama, p.nomor_peserta, p.jenis_kelamin, p.asal_sekolah,
               hk.status_kelulusan, hk.skor_knn, hk.vote_lulus, hk.vote_tidak_lulus, hk.k_value, hk.processed_at,
               CASE WHEN p.nilai_pip IS NOT NULL AND p.nilai_tiu IS NOT NULL
                         AND p.tinggi_badan IS NOT NULL AND p.push_up IS NOT NULL
                    THEN 1 ELSE 0 END AS siap_knn
        FROM peserta p
        LEFT JOIN hasil_knn hk ON hk.id_peserta = p.id
        WHERE p.is_active = 1
        ORDER BY p.created_at DESC, p.id DESC
    ")->fetchAll();

    $totalPeserta  = count($pesertaAll);
    $siapKNN       = count(array_filter($pesertaAll, fn($r)=>$r['siap_knn']));
    $sudahKNN      = count(array_filter($pesertaAll, fn($r)=>$r['status_kelulusan']!==null));
    $totalLulus    = count(array_filter($pesertaAll, fn($r)=>$r['status_kelulusan']==='Lulus'));
    $totalTidak    = count(array_filter($pesertaAll, fn($r)=>$r['status_kelulusan']==='Tidak Lulus'));
    $totalDataLatih= (int)$pdo->query("SELECT COUNT(*) FROM data_latih")->fetchColumn();
} catch (Throwable $e) {
    $pesertaAll = []; $totalPeserta=$siapKNN=$sudahKNN=$totalLulus=$totalTidak=$totalDataLatih=0;
}

$kuotaL = (int)getSetting('kuota_lulus_laki', '35');
$kuotaP = (int)getSetting('kuota_lulus_perempuan', '35');


include __DIR__ . '/../includes/layout.php';
?>

<div class="page-header">
  <div>
    <h2 class="page-title"><span class="icon"><i class="fas fa-brain"></i></span>Proses KNN</h2>
    <p class="page-subtitle">K-Nearest Neighbor — <?= $totalDataLatih ?> data latih tersedia</p>
  </div>
</div>

<!-- Stat Cards -->
<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px;">
  <?php foreach ([
    ['Total Peserta', $totalPeserta, 'fa-users', '#3b82f6'],
    ['Siap KNN',      $siapKNN,     'fa-circle-check', '#8b5cf6'],
    ['Sudah Diproses',$sudahKNN,    'fa-brain', '#f59e0b'],
    ['Lulus',         $totalLulus,  'fa-check-circle', '#10b981'],
    ['Tidak Lulus',   $totalTidak,  'fa-times-circle', '#ef4444'],
    ['Data Latih',    $totalDataLatih,'fa-database', '#6366f1'],
  ] as [$lbl,$val,$ico,$clr]): ?>
  <div class="stat-card" style="border-top:3px solid <?= $clr ?>;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
      <i class="fas <?= $ico ?>" style="color:<?= $clr ?>;font-size:18px;"></i>
      <span style="font-size:26px;font-weight:800;color:#111827;"><?= $val ?></span>
    </div>
    <p style="font-size:11.5px;color:#9ca3af;font-weight:600;margin:0;"><?= $lbl ?></p>
  </div>
  <?php endforeach; ?>
</div>

<div class="dash-row-2" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

  <!-- Proses KNN -->
  <div class="card" style="padding:22px;">
    <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;color:#111827;display:flex;align-items:center;gap:8px;">
      <i class="fas fa-play-circle" style="color:#9f1239;"></i> Jalankan KNN
    </h3>
    <form method="POST" id="formKNN">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" id="formAction" value="proses_satu">
      <div class="form-row form-row-2" style="margin-bottom:14px;">
        <div>
          <label class="form-label">Nilai K</label>
          <select name="k_value" class="form-control form-select">
            <?php for ($ki=1;$ki<=15;$ki+=2): ?><option value="<?=$ki?>" <?=$ki===$kDefault?'selected':''?>>K = <?=$ki?></option><?php endfor; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Pilih Peserta (opsional)</label>
          <select name="id_peserta" id="selPeserta" class="form-control form-select">
            <option value="">— Semua Peserta —</option>
            <?php foreach ($pesertaAll as $p): if (!$p['siap_knn']) continue; ?>
            <option value="<?=$p['id']?>">[<?=htmlspecialchars($p['nomor_peserta']??'?')?>] <?=htmlspecialchars($p['nama'])?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button type="button" class="btn btn-primary" onclick="
          var sel = document.getElementById('selPeserta');
          if (!sel.value) {
            Swal.fire({
              html: '<div style=\'text-align:center;padding:6px 0 2px;\'>' +
                '<div style=\'width:56px;height:56px;border-radius:50%;background:#fff7ed;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(245,158,11,.2);\'>' +
                  '<i class=\'fas fa-user-slash\' style=\'font-size:22px;color:#f59e0b;\'></i>' +
                '</div>' +
                '<h3 style=\'font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:Plus Jakarta Sans,sans-serif;\'>Pilih Peserta Dulu</h3>' +
                '<p style=\'font-size:13.5px;color:#9b8b8b;margin:0;line-height:1.6;\'>Silakan pilih satu peserta dari dropdown, atau gunakan tombol <strong>Proses Semua</strong>.</p>' +
              '</div>',
              confirmButtonText: '<i class=\'fas fa-check\'></i>&nbsp;Mengerti',
              confirmButtonColor: '#9f1239',
              width: 360, padding: '28px 28px 24px', borderRadius: '20px',
              backdrop: 'rgba(15,10,10,0.45)',
              customClass: { popup: 'swal-popup-custom' },
            });
            return;
          }
          document.getElementById('formAction').value = 'proses_satu';
          document.getElementById('formKNN').submit();
        ">
          <i class="fas fa-user"></i> Proses Satu Peserta
        </button>
        <button type="button" class="btn btn-warning"
                onclick="konfirmasiProsesSemuaKNN(<?=$siapKNN?>)">
          <i class="fas fa-users"></i> Proses Semua (<?= $siapKNN ?>)
        </button>
      </div>
    </form>
    <div class="divider"></div>
    <!-- Reset -->
    <form method="POST" style="display:inline;">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="reset_knn">
      <button type="button" class="btn btn-sm btn-secondary"
              onclick="konfirmasiResetKNN(this.closest('form'))">
        <i class="fas fa-rotate-left"></i> Reset Semua Hasil KNN
      </button>
    </form>
  </div>

  <!-- Terapkan Kuota -->
  <div class="card" style="padding:22px;">
    <h3 style="font-size:15px;font-weight:700;margin:0 0 4px;color:#111827;display:flex;align-items:center;gap:8px;">
      <i class="fas fa-filter" style="color:#059669;"></i> Terapkan Kuota Kelulusan
    </h3>
    <p style="font-size:12px;color:#9ca3af;margin:0 0 14px;">Urutkan berdasarkan voting terbanyak dan jarak terdekat, lalu tetapkan kuota. Peserta yang <strong>tidak lulus KNN</strong> tidak akan terpengaruh kuota.</p>
    <form method="POST" id="formTerapkanKuota">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="terapkan_kuota">
      <div class="form-row form-row-2" style="margin-bottom:14px;">
        <div>
          <label class="form-label">Kuota Laki-laki</label>
          <input type="number" name="kuota_l" id="inputKuotaL" class="form-control" min="0" max="200" value="<?= $kuotaL ?>" required>
        </div>
        <div>
          <label class="form-label">Kuota Perempuan</label>
          <input type="number" name="kuota_p" id="inputKuotaP" class="form-control" min="0" max="200" value="<?= $kuotaP ?>" required>
        </div>
      </div>
      <?php if ($sudahKNN > 0): ?>
      <button type="button" class="btn btn-success" onclick="konfirmasiTerapkanKuota()">
        <i class="fas fa-check-double"></i> Terapkan Kuota
      </button>
      <?php else: ?>
      <p class="text-muted"><i class="fas fa-info-circle"></i> Jalankan proses KNN terlebih dahulu.</p>
      <?php endif; ?>
    </form>
  </div>
</div>

<script>
function konfirmasiTerapkanKuota() {
  var kL = parseInt(document.getElementById('inputKuotaL').value) || 0;
  var kP = parseInt(document.getElementById('inputKuotaP').value) || 0;
  Swal.fire({
    html: `<div style="text-align:center;padding:6px 0 2px;">
      <div style="width:56px;height:56px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(5,150,105,.2);">
        <i class="fas fa-filter" style="font-size:22px;color:#059669;"></i>
      </div>
      <h3 style="font-size:17px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Terapkan Kuota Kelulusan?</h3>
      <p style="font-size:13px;color:#6b7280;margin:0 0 14px;line-height:1.6;">Kuota akan diterapkan pada hasil KNN yang sudah ada:</p>
      <div style="display:flex;gap:10px;justify-content:center;margin-bottom:12px;">
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:10px 18px;">
          <div style="font-size:11px;color:#3b82f6;font-weight:600;">♂ Laki-laki</div>
          <div style="font-size:22px;font-weight:800;color:#1d4ed8;">${kL}</div>
          <div style="font-size:10px;color:#6b7280;">peserta</div>
        </div>
        <div style="background:#fdf4ff;border:1px solid #e9d5ff;border-radius:10px;padding:10px 18px;">
          <div style="font-size:11px;color:#a855f7;font-weight:600;">♀ Perempuan</div>
          <div style="font-size:22px;font-weight:800;color:#7c3aed;">${kP}</div>
          <div style="font-size:10px;color:#6b7280;">peserta</div>
        </div>
      </div>
      <p style="font-size:12px;color:#9ca3af;margin:0;"><i class="fas fa-info-circle" style="margin-right:4px;"></i>Peserta yang tidak lulus KNN tidak akan diubah statusnya.</p>
    </div>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-check-double"></i>&nbsp;Ya, Terapkan Kuota',
    cancelButtonText: '<i class="fas fa-times"></i>&nbsp;Batal',
    confirmButtonColor: '#059669', cancelButtonColor: '#f3f4f6',
    width: 420, padding: '28px 28px 24px', borderRadius: '20px',
    backdrop: 'rgba(15,10,10,0.55)',
    customClass: { popup: 'swal-popup-custom', cancelButton: 'swal-cancel-grey' },
  }).then(r => { if (r.isConfirmed) document.getElementById('formTerapkanKuota').submit(); });
}
</script>


<!-- ══ BLOK KETERANGAN KELULUSAN ══ -->
<?php if ($sudahKNN > 0):
  $lulusL   = count(array_filter($pesertaAll, fn($r)=>$r['status_kelulusan']==='Lulus' && $r['jenis_kelamin']==='L'));
  $lulusP   = count(array_filter($pesertaAll, fn($r)=>$r['status_kelulusan']==='Lulus' && $r['jenis_kelamin']==='P'));
  $tidakL   = count(array_filter($pesertaAll, fn($r)=>$r['status_kelulusan']==='Tidak Lulus' && $r['jenis_kelamin']==='L'));
  $tidakP   = count(array_filter($pesertaAll, fn($r)=>$r['status_kelulusan']==='Tidak Lulus' && $r['jenis_kelamin']==='P'));
?>
<div class="card" style="padding:22px;margin-bottom:20px;border-left:4px solid #9f1239;">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
    <div style="width:38px;height:38px;background:#fef2f2;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-info-circle" style="color:#9f1239;font-size:17px;"></i>
    </div>
    <div>
      <h3 style="font-size:14px;font-weight:800;color:#111827;margin:0;">Keterangan Hasil Klasifikasi KNN</h3>
      <p style="font-size:12px;color:#9ca3af;margin:0;">Penjelasan dasar keputusan lulus / tidak lulus berdasarkan algoritma K-Nearest Neighbor</p>
    </div>
  </div>

  <!-- Ringkasan -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:18px;">
    <div style="padding:12px;background:#f0fdf4;border-radius:10px;text-align:center;">
      <div style="font-size:22px;font-weight:800;color:#059669;"><?= $lulusL + $lulusP ?></div>
      <div style="font-size:11px;color:#065f46;font-weight:600;">Total Lulus</div>
    </div>
    <div style="padding:12px;background:#fef2f2;border-radius:10px;text-align:center;">
      <div style="font-size:22px;font-weight:800;color:#dc2626;"><?= $tidakL + $tidakP ?></div>
      <div style="font-size:11px;color:#9f1239;font-weight:600;">Total Tidak Lulus</div>
    </div>
    <div style="padding:12px;background:#eff6ff;border-radius:10px;text-align:center;">
      <div style="font-size:22px;font-weight:800;color:#1d4ed8;"><?= $lulusL ?> <span style="font-size:13px;">/ <?= $lulusP ?></span></div>
      <div style="font-size:11px;color:#1e40af;font-weight:600;">Lulus ♂ / ♀</div>
    </div>
    <div style="padding:12px;background:#f5f3ff;border-radius:10px;text-align:center;">
      <div style="font-size:22px;font-weight:800;color:#7c3aed;"><?= $sudahKNN ?></div>
      <div style="font-size:11px;color:#5b21b6;font-weight:600;">Sudah Diproses</div>
    </div>
  </div>

  <!-- Penjelasan Algoritma -->
  <div style="background:#fafafa;border-radius:12px;padding:16px;margin-bottom:14px;">
    <div style="font-size:12.5px;font-weight:700;color:#374151;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
      <i class="fas fa-brain" style="color:#7c3aed;"></i> Bagaimana KNN Menentukan Kelulusan?
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <div style="padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
        <div style="font-size:11px;font-weight:700;color:#059669;margin-bottom:5px;">✅ Kriteria LULUS</div>
        <div style="font-size:11.5px;color:#374151;line-height:1.6;">
          Dari <strong>K tetangga terdekat</strong> dalam data latih, mayoritas (lebih dari setengah) berlabel <em>"Lulus"</em>.
          Semakin tinggi vote Lulus dan semakin kecil jarak Euclidean ke tetangga, semakin kuat prediksi Lulus.
        </div>
      </div>
      <div style="padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
        <div style="font-size:11px;font-weight:700;color:#dc2626;margin-bottom:5px;">❌ Kriteria TIDAK LULUS</div>
        <div style="font-size:11.5px;color:#374151;line-height:1.6;">
          Mayoritas dari K tetangga terdekat berlabel <em>"Tidak Lulus"</em>. Jika vote sama (seri), sistem memutuskan
          <strong>Tidak Lulus</strong> untuk kehati-hatian. Kuota juga dapat mempengaruhi hasil akhir.
        </div>
      </div>
    </div>
  </div>

  <!-- Faktor Penentu -->
  <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 14px;">
    <div style="font-size:11.5px;font-weight:700;color:#92400e;margin-bottom:7px;">
      <i class="fas fa-lightbulb" style="margin-right:4px;"></i> Faktor-Faktor yang Mempengaruhi Kelulusan
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <?php
      $factors = [
        ['PIP','Pengetahuan Ideologi Pancasila','#1d4ed8','#eff6ff'],
        ['TIU','Tes Inteligensi Umum','#1d4ed8','#eff6ff'],
        ['TB','Tinggi Badan','#059669','#f0fdf4'],
        ['BB','Berat Badan','#059669','#f0fdf4'],
        ['Kes.','Status Kesehatan','#dc2626','#fef2f2'],
        ['Push Up','Kebugaran Fisik','#5b21b6','#f5f3ff'],
        ['Sit Up','Kebugaran Fisik','#5b21b6','#f5f3ff'],
        ['Back Up','Kebugaran Fisik','#5b21b6','#f5f3ff'],
        ['Lari','Daya Tahan','#5b21b6','#f5f3ff'],
        ['PBB','Baris Berbaris','#d97706','#fffbeb'],
        ['Kepribadian','Sikap & Karakter','#d97706','#fffbeb'],
      ];
      foreach($factors as [$code,$desc,$clr,$bg]): ?>
      <div title="<?= $desc ?>" style="padding:4px 10px;border-radius:20px;background:<?=$bg?>;border:1px solid rgba(0,0,0,.06);font-size:10.5px;font-weight:600;color:<?=$clr?>;">
        <?=$code?>
      </div>
      <?php endforeach; ?>
    </div>
    <p style="font-size:11px;color:#92400e;margin:8px 0 0;">
      Semua faktor dinormalisasi (Min-Max) sebelum dihitung jaraknya menggunakan <strong>Euclidean Distance</strong>.
      Klik tombol <i class="fas fa-microscope"></i> pada peserta untuk melihat detail perhitungan lengkap.
    </p>
  </div>
</div>
<?php endif; ?>

<!-- Rekap Hasil KNN -->
<div class="card" style="padding:24px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
    <h3 style="font-size:15px;font-weight:700;margin:0;color:#111827;display:flex;align-items:center;gap:8px;">
      <i class="fas fa-table" style="color:#9f1239;"></i> Rekap Hasil KNN Seluruh Peserta
    </h3>
    <a href="laporan.php" class="btn btn-sm btn-success"><i class="fas fa-file-alt"></i> Laporan Akhir</a>
  </div>
  <div class="table-wrap">
    <table class="data-table" id="tblHasilKNN">
      <thead>
        <tr>
          <th>#</th><th>No. Peserta</th><th>Nama</th><th style="text-align:center;">JK</th>
          <th style="text-align:center;">Siap KNN</th><th style="text-align:center;">K</th>
          <th style="text-align:center;">Vote L</th><th style="text-align:center;">Vote TL</th>
          <th style="text-align:center;">Jarak</th><th style="text-align:center;">Hasil KNN</th>
          <th style="min-width:180px;">Keterangan Hasil</th>
          <th>Diproses</th>
          <th style="text-align:center;width:80px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pesertaAll as $i => $p): ?>
        <tr>
          <td style="color:#9ca3af;font-size:12px;"><?= $i+1 ?></td>
          <td style="font-family:monospace;font-size:12px;color:#6b7280;"><?= htmlspecialchars($p['nomor_peserta']??'—') ?></td>
          <td style="font-weight:600;"><?= htmlspecialchars($p['nama']) ?></td>
          <td style="text-align:center;"><?= jkLabel($p['jenis_kelamin']??'') ?></td>
          <td style="text-align:center;">
            <?= $p['siap_knn']
              ? '<span class="badge badge-success">Siap</span>'
              : '<span class="badge badge-warning">Belum Lengkap</span>' ?>
          </td>
          <td style="text-align:center;font-weight:600;color:#6b7280;"><?= $p['k_value'] ?? '—' ?></td>
          <td style="text-align:center;font-weight:700;color:#059669;"><?= $p['vote_lulus'] ?? '—' ?></td>
          <td style="text-align:center;font-weight:700;color:#dc2626;"><?= $p['vote_tidak_lulus'] ?? '—' ?></td>
          <td style="text-align:center;font-family:monospace;font-size:12px;color:#7c3aed;"><?= $p['skor_knn'] !== null ? number_format($p['skor_knn'],6) : '—' ?></td>
          <td style="text-align:center;"><?= statusBadge($p['status_kelulusan']??'') ?></td>
          <td style="font-size:12px;">
            <?php if ($p['status_kelulusan'] !== null):
              $isLulus = $p['status_kelulusan'] === 'Lulus';
              $vL = (int)($p['vote_lulus'] ?? 0);
              $vTL = (int)($p['vote_tidak_lulus'] ?? 0);
              $k_used = (int)($p['k_value'] ?? 3);
              if ($isLulus): ?>
                <div style="display:flex;flex-direction:column;gap:3px;">
                  <span style="color:#059669;font-weight:700;font-size:11px;display:flex;align-items:center;gap:4px;">
                    <i class="fas fa-check-circle"></i> LULUS
                  </span>
                  <span style="color:#6b7280;font-size:10.5px;">Vote: <?= $vL ?>/<?= $k_used ?> tetangga mendukung Lulus</span>
                  <?php if (!empty($p['keterangan_kuota'])): ?>
                  <span style="color:#d97706;font-size:10px;font-style:italic;"><?= htmlspecialchars($p['keterangan_kuota']) ?></span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:3px;">
                  <span style="color:#dc2626;font-weight:700;font-size:11px;display:flex;align-items:center;gap:4px;">
                    <i class="fas fa-times-circle"></i> TIDAK LULUS
                  </span>
                  <span style="color:#6b7280;font-size:10.5px;">Vote: <?= $vTL ?>/<?= $k_used ?> tetangga mendukung Tidak Lulus</span>
                  <?php if (!empty($p['keterangan_kuota'])): ?>
                  <span style="color:#dc2626;font-size:10px;font-style:italic;"><?= htmlspecialchars($p['keterangan_kuota']) ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            <?php elseif ($p['siap_knn']): ?>
              <span style="color:#9ca3af;font-size:11px;font-style:italic;">Belum diproses</span>
            <?php else: ?>
              <span style="color:#d1d5db;font-size:11px;">Nilai belum lengkap</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:#9ca3af;"><?= $p['processed_at'] ? date('d/m/Y H:i',strtotime($p['processed_at'])) : '—' ?></td>
          <td style="text-align:center;">
            <div style="display:flex;gap:4px;justify-content:center;">
            <?php if ($p['siap_knn']): ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="action" value="proses_satu">
              <input type="hidden" name="id_peserta" value="<?= $p['id'] ?>">
              <input type="hidden" name="k_value" value="<?= $kDefault ?>">
              <button type="submit" class="btn btn-sm btn-primary btn-icon" title="Proses KNN">
                <i class="fas fa-brain"></i>
              </button>
            </form>
            <a href="detail_knn.php?id=<?= $p['id'] ?>&k=<?= $kDefault ?>" class="btn btn-sm btn-secondary btn-icon" title="Lihat Detail Perhitungan KNN">
              <i class="fas fa-microscope"></i>
            </a>
            <?php else: ?>
            <a href="nilai.php?peserta=<?= $p['id'] ?>" class="btn btn-sm btn-warning btn-icon" title="Input Nilai Dulu">
              <i class="fas fa-clipboard"></i>
            </a>
            <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$extraJs = '<script src="' . BASE_URL . '/assets/js/knn.js"></script>';
include __DIR__ . '/../includes/layout_end.php';
?>
