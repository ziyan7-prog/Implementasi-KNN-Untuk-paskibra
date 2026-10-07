<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
requireNotSuperAdmin();

$pdo   = getDB();
$_page = 'nilai';
$pageTitle    = 'Input Nilai Peserta';
$pageSubtitle = 'Masukkan dan kelola nilai per tahap seleksi';
$csrf = generateCSRF();

// ─────────────────────────────────────────────────────────
//  POST HANDLER
// ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        flash('Token tidak valid.', 'error');
        header('Location: nilai.php'); exit;
    }

    $action    = $_POST['action'] ?? '';
    $idPeserta = (int)($_POST['id_peserta'] ?? 0);
    $tabRedir  = '';

    // ── TAHAP 1: Akademis ───────────────────────────────
    if ($action === 'simpan_t1' && $idPeserta) {
        $pip = floatval($_POST['nilai_pip'] ?? 0);
        $tiu = floatval($_POST['nilai_tiu'] ?? 0);
        $err = [];
        if ($pip < 0 || $pip > 100) $err[] = 'Nilai PIP harus antara 0–100.';
        if ($tiu < 0 || $tiu > 100) $err[] = 'Nilai TIU harus antara 0–100.';
        if (empty($err)) {
            $pdo->prepare("UPDATE peserta SET nilai_pip=?, nilai_tiu=?, updated_at=NOW()
                           WHERE id=? AND is_active=1")->execute([$pip, $tiu, $idPeserta]);
            $pdo->prepare("DELETE FROM hasil_knn WHERE id_peserta=?")->execute([$idPeserta]);
            $s=$pdo->prepare("SELECT nama FROM peserta WHERE id=? LIMIT 1"); $s->execute([$idPeserta]);
            $nama=$s->fetchColumn();
            catatLog('SIMPAN_NILAI_T1',"Peserta=$nama ID=$idPeserta PIP=$pip TIU=$tiu");
            flash("Nilai Tahap 1 <strong>$nama</strong> berhasil disimpan. (PIP: $pip | TIU: $tiu)");
        } else { flash(implode(' ',$err),'error'); }
        header("Location: nilai.php?peserta=$idPeserta&tab=1"); exit;

    // ── TAHAP 2: Kesehatan ──────────────────────────────
    } elseif ($action === 'simpan_t2' && $idPeserta) {
        $tb    = floatval($_POST['tinggi_badan'] ?? 0);
        $bb    = floatval($_POST['berat_badan']  ?? 0);
        $kes   = $_POST['kesehatan'] ?? '';
        $err = [];
        if ($tb < 100 || $tb > 250)   $err[] = 'Tinggi badan harus antara 100–250 cm.';
        if ($bb < 30  || $bb > 200)   $err[] = 'Berat badan harus antara 30–200 kg.';
        if (!in_array($kes,['0','1'])) $err[] = 'Status kesehatan wajib dipilih.';
        if (empty($err)) {
            $pdo->prepare("UPDATE peserta SET tinggi_badan=?,berat_badan=?,kesehatan=?,updated_at=NOW()
                           WHERE id=? AND is_active=1")
                ->execute([$tb,$bb,(int)$kes,$idPeserta]);
            $pdo->prepare("DELETE FROM hasil_knn WHERE id_peserta=?")->execute([$idPeserta]);
            $s=$pdo->prepare("SELECT nama FROM peserta WHERE id=? LIMIT 1"); $s->execute([$idPeserta]);
            $nama=$s->fetchColumn();
            catatLog('SIMPAN_NILAI_T2',"Peserta=$nama ID=$idPeserta TB=$tb BB=$bb Kes=$kes");
            flash("Nilai Tahap 2 <strong>$nama</strong> berhasil disimpan. (TB: {$tb} cm | BB: {$bb} kg | Kes: ".($kes=='1'?'Sehat':'Tidak Sehat').")");
        } else { flash(implode(' ',$err),'error'); }
        header("Location: nilai.php?peserta=$idPeserta&tab=2"); exit;

    // ── TAHAP 3: Fisik & Kepribadian ────────────────────
    } elseif ($action === 'simpan_t3' && $idPeserta) {
        $pu  = floatval($_POST['push_up']     ?? 0);
        $su  = floatval($_POST['sit_up']      ?? 0);
        $bu  = floatval($_POST['back_up']     ?? 0);
        $lr  = floatval($_POST['lari']        ?? 0);
        $pb  = floatval($_POST['pbb']         ?? 0);
        $kep = floatval($_POST['kepribadian'] ?? 0);
        $ket = sanitize($_POST['keterangan']  ?? '');
        $err = [];
        if ($pu  < 0 || $pu  > 500) $err[]='Push Up harus antara 0–500 repetisi.';
        if ($su  < 0 || $su  > 500) $err[]='Sit Up harus antara 0–500 repetisi.';
        if ($bu  < 0 || $bu  > 500) $err[]='Back Up harus antara 0–500 repetisi.';
        if ($lr  < 0 || $lr  > 15)  $err[]='Lari harus antara 0–15 putaran.';
        if ($pb  < 0 || $pb  > 100) $err[]='Nilai PBB harus antara 0–100.';
        if ($kep < 0 || $kep > 100) $err[]='Nilai Kepribadian harus antara 0–100.';
        if (empty($err)) {
            $pdo->prepare("UPDATE peserta SET push_up=?,sit_up=?,back_up=?,lari=?,
                           pbb=?,kepribadian=?,keterangan_nilai=?,updated_at=NOW()
                           WHERE id=? AND is_active=1")
                ->execute([$pu,$su,$bu,$lr,$pb,$kep,$ket,$idPeserta]);
            $pdo->prepare("DELETE FROM hasil_knn WHERE id_peserta=?")->execute([$idPeserta]);
            $s=$pdo->prepare("SELECT nama FROM peserta WHERE id=? LIMIT 1"); $s->execute([$idPeserta]);
            $nama=$s->fetchColumn();
            catatLog('SIMPAN_NILAI_T3',"Peserta=$nama ID=$idPeserta PushUp=$pu SitUp=$su Lari=$lr");
            flash("Nilai Tahap 3 <strong>$nama</strong> berhasil disimpan.");
        } else { flash(implode(' ',$err),'error'); }
        header("Location: nilai.php?peserta=$idPeserta&tab=3"); exit;

    // ── HAPUS SEMUA ──────────────────────────────────────
    } elseif ($action === 'hapus_nilai' && $idPeserta) {
        $pdo->prepare("UPDATE peserta SET nilai_pip=NULL,nilai_tiu=NULL,tinggi_badan=NULL,berat_badan=NULL,
            kesehatan=NULL,push_up=NULL,sit_up=NULL,back_up=NULL,lari=NULL,pbb=NULL,kepribadian=NULL,
            keterangan_nilai=NULL,updated_at=NOW() WHERE id=?")->execute([$idPeserta]);
        $pdo->prepare("DELETE FROM hasil_knn WHERE id_peserta=?")->execute([$idPeserta]);
        catatLog('HAPUS_NILAI',"ID=$idPeserta");
        flash('Semua nilai peserta berhasil dihapus.','warning');
        header('Location: nilai.php'); exit;
    }
}

// ── GET DATA ──────────────────────────────────────────────
$pesertaList = $pdo->query("
    SELECT id,nama,nomor_peserta,jenis_kelamin,asal_sekolah,
           nilai_pip,nilai_tiu,tinggi_badan,berat_badan,kesehatan,
           push_up,sit_up,back_up,lari,pbb,kepribadian,
           keterangan_nilai,updated_at,
           CASE WHEN nilai_pip IS NOT NULL AND nilai_tiu IS NOT NULL THEN 1 ELSE 0 END AS t1_done,
           CASE WHEN tinggi_badan IS NOT NULL AND berat_badan IS NOT NULL AND kesehatan IS NOT NULL THEN 1 ELSE 0 END AS t2_done,
           CASE WHEN push_up IS NOT NULL AND sit_up IS NOT NULL AND lari IS NOT NULL AND pbb IS NOT NULL AND kepribadian IS NOT NULL THEN 1 ELSE 0 END AS t3_done,
           CASE WHEN nilai_pip IS NOT NULL AND nilai_tiu IS NOT NULL
                     AND tinggi_badan IS NOT NULL AND push_up IS NOT NULL
                THEN 1 ELSE 0 END AS lengkap
    FROM peserta WHERE is_active=1 ORDER BY created_at DESC, id DESC
")->fetchAll();

$selected  = null;
if (!empty($_GET['peserta'])) {
    foreach ($pesertaList as $p) {
        if ($p['id'] == (int)$_GET['peserta']) { $selected = $p; break; }
    }
}
$activeTab = (int)($_GET['tab'] ?? 0);
if ($selected && ($activeTab < 1 || $activeTab > 3)) {
    if (!$selected['t1_done']) $activeTab = 1;
    elseif (!$selected['t2_done']) $activeTab = 2;
    elseif (!$selected['t3_done']) $activeTab = 3;
    else $activeTab = 1;
}
if ($activeTab < 1) $activeTab = 1;

$statT1  = count(array_filter($pesertaList, fn($r)=>$r['t1_done']));
$statT2  = count(array_filter($pesertaList, fn($r)=>$r['t2_done']));
$statT3  = count(array_filter($pesertaList, fn($r)=>$r['t3_done']));
$statLen = count(array_filter($pesertaList, fn($r)=>$r['lengkap']));
$total   = count($pesertaList);

include __DIR__ . '/../includes/layout.php';
?>

<!-- Stat Row -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
<?php foreach ([
  ['Tahap 1 — Akademis','fa-book-open','#1d4ed8','#eff6ff',$statT1],
  ['Tahap 2 — Kesehatan','fa-heartbeat','#065f46','#f0fdf4',$statT2],
  ['Tahap 3 — Fisik','fa-dumbbell','#5b21b6','#f5f3ff',$statT3],
  ['Semua Tahap Lengkap','fa-check-circle','#059669','#ecfdf5',$statLen],
] as [$lbl,$ico,$clr,$bg,$val]): ?>
<div class="card" style="padding:14px 16px;display:flex;align-items:center;gap:12px;">
  <div style="width:38px;height:38px;border-radius:10px;background:<?=$bg?>;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
    <i class="fas <?=$ico?>" style="font-size:15px;color:<?=$clr?>;"></i>
  </div>
  <div>
    <div style="font-size:21px;font-weight:800;color:#111827;line-height:1;"><?=$val?><span style="font-size:12px;color:#9ca3af;font-weight:500;">/<?=$total?></span></div>
    <div style="font-size:10.5px;color:#9ca3af;margin-top:2px;"><?=$lbl?></div>
  </div>
</div>
<?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:310px 1fr;gap:18px;align-items:start;">

<!-- ── Sidebar Peserta ─────────────────────────────────── -->
<div class="card" style="padding:14px;position:sticky;top:76px;max-height:calc(100vh - 108px);display:flex;flex-direction:column;">
  <div style="font-size:12.5px;font-weight:700;color:#374151;margin-bottom:10px;display:flex;justify-content:space-between;">
    <span><i class="fas fa-list" style="color:#9f1239;margin-right:5px;"></i>Daftar Peserta</span>
    <span style="font-size:11px;color:#9ca3af;"><?=$total?> orang</span>
  </div>
  <input type="text" id="searchPeserta" class="form-control" placeholder="Cari nama / nomor..." style="margin-bottom:8px;font-size:12px;padding:6px 10px;">
  <div style="overflow-y:auto;flex:1;" id="listPeserta">
    <?php foreach ($pesertaList as $p):
      $isAct = ($selected && $selected['id']==$p['id']);
    ?>
    <a href="nilai.php?peserta=<?=$p['id']?>"
       class="peserta-item"
       style="display:flex;align-items:center;gap:8px;padding:7px 9px;border-radius:9px;
              cursor:pointer;text-decoration:none;margin-bottom:2px;
              border:1.5px solid <?=$isAct?'#9f1239':'transparent'?>;
              background:<?=$isAct?'#fef2f2':'transparent'?>;">
      <div style="width:29px;height:29px;border-radius:8px;
                  background:<?=$p['jenis_kelamin']==='L'?'#eff6ff':'#fdf2f8'?>;
                  display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <span style="font-size:11px;font-weight:700;color:<?=$p['jenis_kelamin']==='L'?'#1d4ed8':'#be185d'?>;"><?=strtoupper(mb_substr($p['nama'],0,1))?></span>
      </div>
      <div style="flex:1;min-width:0;">
        <div style="font-size:12px;font-weight:600;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($p['nama'])?></div>
        <div style="font-size:10px;color:#9ca3af;"><?=htmlspecialchars($p['nomor_peserta']??'—')?></div>
      </div>
      <div style="display:flex;gap:3px;flex-shrink:0;" title="T1/T2/T3">
        <?php for($t=1;$t<=3;$t++): $d=$p["t{$t}_done"]; $cs=['#1d4ed8','#059669','#7c3aed'][$t-1]; ?>
        <div style="width:7px;height:7px;border-radius:50%;background:<?=$d?$cs:'#e5e7eb'?>;"></div>
        <?php endfor; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <div style="border-top:1px solid #f3f4f6;padding-top:8px;margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;">
    <?php foreach ([['T1','#1d4ed8'],['T2','#059669'],['T3','#7c3aed']] as [$t,$c]): ?>
    <span style="font-size:9.5px;color:#9ca3af;display:flex;align-items:center;gap:3px;">
      <span style="width:7px;height:7px;border-radius:50%;background:<?=$c?>;display:inline-block;"></span><?=$t?>
    </span>
    <?php endforeach; ?>
    <span style="font-size:9.5px;color:#9ca3af;display:flex;align-items:center;gap:3px;">
      <span style="width:7px;height:7px;border-radius:50%;background:#e5e7eb;display:inline-block;"></span>Belum
    </span>
  </div>
</div>

<!-- ── Panel Kanan ─────────────────────────────────────── -->
<div>
<?php if ($selected): ?>

<!-- Peserta Header -->
<div class="card" style="padding:16px;margin-bottom:14px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
  <div style="width:44px;height:44px;border-radius:12px;background:<?=$selected['jenis_kelamin']==='L'?'#eff6ff':'#fdf2f8'?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
    <span style="font-size:18px;font-weight:800;color:<?=$selected['jenis_kelamin']==='L'?'#1d4ed8':'#be185d'?>;"><?=strtoupper(mb_substr($selected['nama'],0,1))?></span>
  </div>
  <div style="flex:1;">
    <div style="font-size:16px;font-weight:800;color:#111827;"><?=htmlspecialchars($selected['nama'])?></div>
    <div style="font-size:12px;color:#9ca3af;">
      <?=htmlspecialchars($selected['nomor_peserta']??'—')?>&nbsp;·&nbsp;
      <?=$selected['jenis_kelamin']==='L'?'♂ Laki-laki':'♀ Perempuan'?>&nbsp;·&nbsp;
      <?=htmlspecialchars($selected['asal_sekolah']??'—')?>
    </div>
  </div>
  <!-- Chip status tahap -->
  <div style="display:flex;gap:5px;flex-wrap:wrap;">
    <?php foreach ([
      [1,'T1 Akademis','#1d4ed8','#eff6ff','t1_done'],
      [2,'T2 Kesehatan','#059669','#f0fdf4','t2_done'],
      [3,'T3 Fisik','#7c3aed','#f5f3ff','t3_done'],
    ] as [$tn,$lbl,$clr,$bg,$key]): $d=$selected[$key]; ?>
    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;
                 background:<?=$d?$bg:'#f3f4f6'?>;color:<?=$d?$clr:'#9ca3af'?>;
                 display:flex;align-items:center;gap:4px;">
      <i class="fas <?=$d?'fa-check-circle':'fa-clock'?>" style="font-size:8px;"></i><?=$lbl?>
    </span>
    <?php endforeach; ?>
  </div>
  <?php if($selected['updated_at']): ?>
  <div style="text-align:right;flex-shrink:0;">
    <div style="font-size:10px;color:#9ca3af;">Terakhir update</div>
    <div style="font-size:11.5px;font-weight:600;color:#374151;"><?=formatTanggal($selected['updated_at'])?></div>
  </div>
  <?php endif; ?>
</div>

<!-- ── TABS HEADER ── -->
<div style="display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin-bottom:14px;">
  <?php foreach ([
    [1,'📚 Tahap 1 — Akademis','#1d4ed8','t1_done'],
    [2,'🏥 Tahap 2 — Kesehatan','#059669','t2_done'],
    [3,'💪 Tahap 3 — Fisik &amp; Kepribadian','#7c3aed','t3_done'],
  ] as [$tn,$lbl,$clr,$dk]): $isAct=($activeTab===$tn); $isDone=$selected[$dk]; ?>
  <button type="button" id="tabBtn<?=$tn?>" onclick="switchTab(<?=$tn?>)"
    style="padding:9px 16px;font-size:12.5px;font-weight:700;cursor:pointer;border:none;
           background:<?=$isAct?'#fff':'transparent'?>;
           color:<?=$isAct?$clr:'#6b7280'?>;
           border-bottom:2px solid <?=$isAct?$clr:'transparent'?>;
           margin-bottom:-2px;display:flex;align-items:center;gap:6px;
           border-radius:8px 8px 0 0;transition:all .15s;white-space:nowrap;">
    <?=$lbl?> <?=$isDone?'<i class="fas fa-check-circle" style="font-size:10px;"></i>':'<i class="fas fa-circle" style="font-size:8px;color:#d1d5db;"></i>'?>
  </button>
  <?php endforeach; ?>
</div>

<!-- ════ PANEL 1: Akademis ════ -->
<div id="tabPanel1" style="display:<?=$activeTab===1?'block':'none'?>;">
<div class="card" style="padding:20px;margin-bottom:12px;">
  <h4 style="font-size:13.5px;font-weight:700;color:#1d4ed8;margin:0 0 14px;display:flex;align-items:center;gap:7px;border-bottom:2px solid #eff6ff;padding-bottom:9px;">
    <i class="fas fa-book-open"></i> Tahap 1 — Penilaian Akademis
    <span style="font-size:11px;font-weight:400;color:#9ca3af;">Rentang nilai: 0–100</span>
  </h4>
  <?php if($selected['t1_done']): ?>
  <div style="margin-bottom:12px;padding:9px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:9px;font-size:12px;color:#1d4ed8;display:flex;align-items:center;gap:7px;">
    <i class="fas fa-circle-info"></i> Nilai Tahap 1 sudah tersimpan. Edit form di bawah untuk memperbarui.
  </div>
  <?php endif; ?>
  <form method="POST" id="formT1">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>">
    <input type="hidden" name="action" value="simpan_t1">
    <input type="hidden" name="id_peserta" value="<?=$selected['id']?>">
    <div class="form-row form-row-3">
      <div class="form-group">
        <label class="form-label">Nilai PIP (0–100) <span style="color:#dc2626;">*</span></label>
        <input type="number" name="nilai_pip" id="inPIP" class="form-control"
               min="0" max="100" step="any" required placeholder="Cth: 80.00"
               value="<?=$selected['nilai_pip']??''?>" oninput="updRata()">
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Pengetahuan Ideologi Pancasila — 0.00–100.00</div>
      </div>
      <div class="form-group">
        <label class="form-label">Nilai TIU (0–100) <span style="color:#dc2626;">*</span></label>
        <input type="number" name="nilai_tiu" id="inTIU" class="form-control"
               min="0" max="100" step="any" required placeholder="Cth: 75.00"
               value="<?=$selected['nilai_tiu']??''?>" oninput="updRata()">
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Tes Inteligensi Umum — 0.00–100.00</div>
      </div>
      <div class="form-group">
        <label class="form-label">Rata-rata Akademis</label>
        <div class="form-control" id="rataAkad" style="background:#f9fafb;font-weight:700;
             color:<?=($selected['nilai_pip']!==null&&$selected['nilai_tiu']!==null)?'#1d4ed8':'#9ca3af'?>;">
          <?=($selected['nilai_pip']!==null&&$selected['nilai_tiu']!==null)
             ?number_format(($selected['nilai_pip']+$selected['nilai_tiu'])/2,2):'—'?>
        </div>
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Otomatis dihitung</div>
      </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:4px;">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Tahap 1</button>
      <a href="nilai.php?peserta=<?=$selected['id']?>&tab=1" class="btn btn-secondary" style="font-size:12px;">Reset</a>
      <?php if($selected['t1_done']): ?>
      <button type="button" class="btn btn-danger btn-sm"
              onclick="hapusTahap(<?=$selected['id']?>,'Tahap 1 (Akademis)','<?=addslashes(htmlspecialchars($selected['nama']))?>')">
        <i class="fas fa-trash"></i> Hapus T1
      </button>
      <button type="button" onclick="switchTab(2)" style="margin-left:auto;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:12px;" class="btn btn-sm">
        Lanjut ke Tahap 2 <i class="fas fa-arrow-right"></i>
      </button>
      <?php endif; ?>
    </div>
  </form>
</div>
</div>

<!-- ════ PANEL 2: Kesehatan ════ -->
<div id="tabPanel2" style="display:<?=$activeTab===2?'block':'none'?>;">
<div class="card" style="padding:20px;margin-bottom:12px;">
  <h4 style="font-size:13.5px;font-weight:700;color:#065f46;margin:0 0 14px;display:flex;align-items:center;gap:7px;border-bottom:2px solid #f0fdf4;padding-bottom:9px;">
    <i class="fas fa-heartbeat"></i> Tahap 2 — Penilaian Kesehatan
  </h4>
  <?php if(!$selected['t1_done']): ?>
  <div style="margin-bottom:12px;padding:9px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;font-size:12px;color:#92400e;display:flex;align-items:center;gap:7px;">
    <i class="fas fa-triangle-exclamation"></i> Sebaiknya lengkapi <strong>Tahap 1 (Akademis)</strong> terlebih dahulu.
  </div>
  <?php elseif($selected['t2_done']): ?>
  <div style="margin-bottom:12px;padding:9px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;font-size:12px;color:#065f46;display:flex;align-items:center;gap:7px;">
    <i class="fas fa-circle-info"></i> Nilai Tahap 2 sudah tersimpan. Edit untuk memperbarui.
  </div>
  <?php endif; ?>
  <form method="POST" id="formT2">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>">
    <input type="hidden" name="action" value="simpan_t2">
    <input type="hidden" name="id_peserta" value="<?=$selected['id']?>">
    <div class="form-row form-row-3" style="margin-bottom:12px;">
      <div class="form-group">
        <label class="form-label">Tinggi Badan (cm) <span style="color:#dc2626;">*</span></label>
        <input type="number" name="tinggi_badan" id="inTB" class="form-control"
               min="100" max="250" step="any" required placeholder="100–250"
               value="<?=$selected['tinggi_badan']??''?>" oninput="updBMI2()">
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Rentang: 100–250 cm</div>
      </div>
      <div class="form-group">
        <label class="form-label">Berat Badan (kg) <span style="color:#dc2626;">*</span></label>
        <input type="number" name="berat_badan" id="inBB" class="form-control"
               min="30" max="200" step="any" required placeholder="30–200"
               value="<?=$selected['berat_badan']??''?>" oninput="updBMI2()">
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Rentang: 30–200 kg</div>
      </div>
      <div class="form-group">
        <label class="form-label">BMI (Indeks Massa Tubuh)</label>
        <div class="form-control" id="bmiResult" style="background:#f9fafb;font-weight:700;">
          <?php if($selected['tinggi_badan']&&$selected['berat_badan']):
            $bmi=round($selected['berat_badan']/pow($selected['tinggi_badan']/100,2),1);
            $bc=$bmi<18.5?'#d97706':($bmi<25?'#059669':($bmi<30?'#d97706':'#dc2626'));
            echo "<span style='color:$bc'>$bmi</span> <span style='font-size:11px;color:#9ca3af;'>kg/m²</span>";
          else: echo '<span style="color:#9ca3af">—</span>'; endif; ?>
        </div>
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Otomatis dihitung</div>
      </div>
    </div>
    <div class="form-row form-row-2" style="margin-bottom:14px;">
      <div class="form-group">
        <label class="form-label">Status Kesehatan <span style="color:#dc2626;">*</span></label>
        <select name="kesehatan" class="form-control form-select" required>
          <option value="">— Pilih Status —</option>
          <option value="1" <?=($selected['kesehatan']==='1'||$selected['kesehatan']===1)?'selected':''?>>✅ Sehat</option>
          <option value="0" <?=($selected['kesehatan']==='0'||$selected['kesehatan']===0)?'selected':''?>>❌ Tidak Sehat</option>
        </select>
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Wajib diisi</div>
      </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Tahap 2</button>
      <a href="nilai.php?peserta=<?=$selected['id']?>&tab=2" class="btn btn-secondary" style="font-size:12px;">Reset</a>
      <?php if($selected['t2_done']): ?>
      <button type="button" class="btn btn-danger btn-sm"
              onclick="hapusTahap(<?=$selected['id']?>,'Tahap 2 (Kesehatan)','<?=addslashes(htmlspecialchars($selected['nama']))?>')">
        <i class="fas fa-trash"></i> Hapus T2
      </button>
      <?php endif; ?>
      <button type="button" onclick="switchTab(1)" class="btn btn-sm" style="background:#f9fafb;color:#6b7280;border:1px solid #e5e7eb;font-size:12px;">
        <i class="fas fa-arrow-left"></i> Kembali T1
      </button>
      <?php if($selected['t2_done']): ?>
      <button type="button" onclick="switchTab(3)" class="btn btn-sm" style="margin-left:auto;background:#f0fdf4;color:#059669;border:1px solid #bbf7d0;font-size:12px;">
        Lanjut ke Tahap 3 <i class="fas fa-arrow-right"></i>
      </button>
      <?php endif; ?>
    </div>
  </form>
</div>
</div>

<!-- ════ PANEL 3: Fisik & Kepribadian ════ -->
<div id="tabPanel3" style="display:<?=$activeTab===3?'block':'none'?>;">
<div class="card" style="padding:20px;margin-bottom:12px;">
  <h4 style="font-size:13.5px;font-weight:700;color:#5b21b6;margin:0 0 14px;display:flex;align-items:center;gap:7px;border-bottom:2px solid #f5f3ff;padding-bottom:9px;">
    <i class="fas fa-dumbbell"></i> Tahap 3 — Fisik &amp; Kepribadian
  </h4>
  <?php if(!$selected['t2_done']): ?>
  <div style="margin-bottom:12px;padding:9px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;font-size:12px;color:#92400e;display:flex;align-items:center;gap:7px;">
    <i class="fas fa-triangle-exclamation"></i> Sebaiknya lengkapi <strong>Tahap 2 (Kesehatan)</strong> terlebih dahulu.
  </div>
  <?php elseif($selected['t3_done']): ?>
  <div style="margin-bottom:12px;padding:9px 12px;background:#f5f3ff;border:1px solid #ddd6fe;border-radius:9px;font-size:12px;color:#5b21b6;display:flex;align-items:center;gap:7px;">
    <i class="fas fa-circle-info"></i> Nilai Tahap 3 sudah tersimpan. Edit untuk memperbarui.
  </div>
  <?php endif; ?>
  <form method="POST" id="formT3">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>">
    <input type="hidden" name="action" value="simpan_t3">
    <input type="hidden" name="id_peserta" value="<?=$selected['id']?>">
    <!-- Kebugaran Fisik -->
    <div style="font-size:11.5px;font-weight:700;color:#6b7280;margin-bottom:9px;text-transform:uppercase;letter-spacing:.04em;">
      <i class="fas fa-running" style="margin-right:4px;"></i>Kebugaran Fisik
    </div>
    <div class="form-row form-row-3" style="margin-bottom:14px;">
      <?php foreach ([
        ['push_up','Push Up (rep.)','0','500','1','push_up','0–500 repetisi'],
        ['sit_up', 'Sit Up (rep.)', '0','500','1','sit_up', '0–500 repetisi'],
        ['back_up','Back Up (rep.)','0','500','1','back_up','0–500 repetisi'],
        ['lari',   'Lari (putaran)','0','15', 'any','lari', '0–15 putaran'],
      ] as [$nm,$lbl,$mn,$mx,$st,$fi,$ht]): ?>
      <div class="form-group">
        <label class="form-label"><?=$lbl?> <span style="color:#dc2626;">*</span></label>
        <input type="number" name="<?=$nm?>" class="form-control"
               min="<?=$mn?>" max="<?=$mx?>" step="any" inputmode="decimal" required placeholder="<?=$mn?>"
               value="<?=$selected[$fi]??''?>">
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;"><?=$ht?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Penilaian -->
    <div style="font-size:11.5px;font-weight:700;color:#6b7280;margin-bottom:9px;text-transform:uppercase;letter-spacing:.04em;">
      <i class="fas fa-star" style="margin-right:4px;"></i>Penilaian Baris Berbaris &amp; Kepribadian
    </div>
    <div class="form-row form-row-3" style="margin-bottom:14px;">
      <div class="form-group">
        <label class="form-label">Nilai PBB (0–100) <span style="color:#dc2626;">*</span></label>
        <input type="number" name="pbb" class="form-control"
               min="0" max="100" step="any" required placeholder="0–100"
               value="<?=$selected['pbb']??''?>">
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Peraturan Baris Berbaris</div>
      </div>
      <div class="form-group">
        <label class="form-label">Kepribadian (0–100) <span style="color:#dc2626;">*</span></label>
        <input type="number" name="kepribadian" class="form-control"
               min="0" max="100" step="any" required placeholder="0–100"
               value="<?=$selected['kepribadian']??''?>">
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Penilaian sikap &amp; kepribadian</div>
      </div>
    </div>
    <div class="form-group" style="margin-bottom:14px;">
      <label class="form-label">Keterangan / Catatan (opsional)</label>
      <input type="text" name="keterangan" class="form-control"
             value="<?=htmlspecialchars($selected['keterangan_nilai']??'')?>"
             placeholder="Catatan tambahan...">
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <button type="submit" class="btn" style="background:#5b21b6;color:#fff;font-weight:700;">
        <i class="fas fa-save"></i> Simpan Tahap 3
      </button>
      <a href="nilai.php?peserta=<?=$selected['id']?>&tab=3" class="btn btn-secondary" style="font-size:12px;">Reset</a>
      <?php if($selected['t3_done']): ?>
      <button type="button" class="btn btn-danger btn-sm"
              onclick="hapusTahap(<?=$selected['id']?>,'Tahap 3 (Fisik)','<?=addslashes(htmlspecialchars($selected['nama']))?>')">
        <i class="fas fa-trash"></i> Hapus T3
      </button>
      <?php endif; ?>
      <button type="button" onclick="switchTab(2)" class="btn btn-sm" style="background:#f9fafb;color:#6b7280;border:1px solid #e5e7eb;font-size:12px;">
        <i class="fas fa-arrow-left"></i> Kembali T2
      </button>
      <?php if($selected['t1_done']&&$selected['t2_done']&&$selected['t3_done']): ?>
      <a href="proses_knn.php" class="btn btn-sm" style="margin-left:auto;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;font-size:12px;">
        <i class="fas fa-brain"></i> Proses KNN Sekarang
      </a>
      <?php endif; ?>
    </div>
  </form>
</div>
</div>

<!-- Hapus Semua Tombol -->
<?php if($selected['t1_done']||$selected['t2_done']||$selected['t3_done']): ?>
<div style="display:flex;justify-content:flex-end;margin-bottom:14px;">
  <button type="button" class="btn btn-danger btn-sm"
          onclick="hapusNilai(<?=$selected['id']?>,'<?=addslashes(htmlspecialchars($selected['nama']))?>')">
    <i class="fas fa-trash-alt"></i> Hapus Semua Nilai Peserta Ini
  </button>
</div>
<?php endif; ?>

<?php else: ?>
<div class="card" style="padding:60px 24px;text-align:center;">
  <i class="fas fa-hand-pointer" style="font-size:48px;color:#e5e7eb;display:block;margin-bottom:16px;"></i>
  <p style="font-size:16px;font-weight:600;color:#374151;margin:0 0 8px;">Pilih Peserta</p>
  <p style="font-size:13px;color:#9ca3af;margin:0;">Klik nama peserta di panel kiri untuk memulai input nilai per tahap.</p>
</div>
<?php endif; ?>
</div><!-- /panel kanan -->
</div><!-- /grid -->

<!-- Form hapus tersembunyi -->
<form id="formHapusNilai" method="POST" style="display:none;">
  <input type="hidden" name="csrf_token" value="<?=$csrf?>">
  <input type="hidden" name="action" value="hapus_nilai">
  <input type="hidden" name="id_peserta" id="hapusNilaiId">
</form>

<!-- ══ REKAP NILAI ══ -->
<div class="card" style="padding:24px;margin-top:22px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
    <div>
      <h3 style="font-size:15px;font-weight:700;margin:0;color:#111827;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-table" style="color:#9f1239;"></i> Rekap Nilai Seluruh Peserta
      </h3>
      <p style="font-size:12px;color:#9ca3af;margin:4px 0 0;"><?=$statLen?> dari <?=$total?> peserta memiliki nilai lengkap (siap KNN)</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-sm btn-secondary" onclick="exportRekapCSV()"><i class="fas fa-file-csv"></i> Export CSV</button>
      <a href="proses_knn.php" class="btn btn-sm btn-primary"><i class="fas fa-brain"></i> Proses KNN</a>
    </div>
  </div>
  <!-- Progress bar -->
  <?php $pct=$total>0?round($statLen/$total*100):0; ?>
  <div style="margin-bottom:14px;">
    <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
      <span>Kelengkapan Nilai (3 Tahap Lengkap)</span>
      <span style="color:#9ca3af;"><?=$pct?>%</span>
    </div>
    <div style="height:7px;background:#f3f4f6;border-radius:99px;overflow:hidden;margin-bottom:6px;">
      <div style="height:100%;width:<?=$pct?>%;background:<?=$pct==100?'#10b981':'#9f1239'?>;border-radius:99px;transition:width .5s;"></div>
    </div>
    <div style="display:flex;gap:14px;flex-wrap:wrap;">
      <?php foreach ([['T1 Akademis','#1d4ed8',$statT1],['T2 Kesehatan','#059669',$statT2],['T3 Fisik','#7c3aed',$statT3]] as [$l,$c,$v]):
        $p2=$total>0?round($v/$total*100):0; ?>
      <div style="display:flex;align-items:center;gap:5px;">
        <div style="width:24px;height:4px;border-radius:2px;background:<?=$c?>;"></div>
        <span style="font-size:10.5px;color:#9ca3af;"><?=$l?>: <?=$v?>/<?=$total?> (<?=$p2?>%)</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="table-wrap">
    <table class="data-table" id="tblRekapNilai">
      <thead><tr>
        <th style="width:30px;">#</th><th>No. Peserta</th><th>Nama</th>
        <th style="text-align:center;">JK</th>
        <th style="text-align:center;background:#eff6ff;color:#1d4ed8;">PIP</th>
        <th style="text-align:center;background:#eff6ff;color:#1d4ed8;">TIU</th>
        <th style="text-align:center;background:#f0fdf4;color:#065f46;">TB</th>
        <th style="text-align:center;background:#f0fdf4;color:#065f46;">BB</th>
        <th style="text-align:center;background:#f0fdf4;color:#065f46;">Kes.</th>
        <th style="text-align:center;background:#f5f3ff;color:#5b21b6;">Push</th>
        <th style="text-align:center;background:#f5f3ff;color:#5b21b6;">Sit</th>
        <th style="text-align:center;background:#f5f3ff;color:#5b21b6;">Back</th>
        <th style="text-align:center;background:#f5f3ff;color:#5b21b6;">Lari</th>
        <th style="text-align:center;background:#f5f3ff;color:#5b21b6;">PBB</th>
        <th style="text-align:center;background:#f5f3ff;color:#5b21b6;">Keprib.</th>
        <th style="text-align:center;width:32px;">T1</th>
        <th style="text-align:center;width:32px;">T2</th>
        <th style="text-align:center;width:32px;">T3</th>
        <th style="text-align:center;width:70px;">Aksi</th>
      </tr></thead>
      <tbody>
        <?php
        function n($v,$d=1){ return $v!==null?number_format((float)$v,$d):'<span style="color:#d1d5db;">—</span>'; }
        function tck($done){ return $done?'<span style="color:#059669;font-size:12px;">✓</span>':'<span style="color:#e5e7eb;font-size:11px;">○</span>'; }
        foreach($pesertaList as $i=>$p):
          $hasAny=($p['t1_done']||$p['t2_done']||$p['t3_done']);
        ?>
        <tr id="row-<?=$p['id']?>" style="<?=($selected&&$selected['id']==$p['id'])?'background:#fef2f2;':''?>">
          <td style="color:#9ca3af;font-size:12px;"><?=$i+1?></td>
          <td style="font-family:monospace;font-size:12px;color:#6b7280;"><?=htmlspecialchars($p['nomor_peserta']??'—')?></td>
          <td>
            <a href="nilai.php?peserta=<?=$p['id']?>" style="font-weight:600;color:#111827;text-decoration:none;"><?=htmlspecialchars($p['nama'])?></a>
            <?php if($p['keterangan_nilai']): ?><div style="font-size:10.5px;color:#9ca3af;"><?=htmlspecialchars($p['keterangan_nilai'])?></div><?php endif; ?>
          </td>
          <td style="text-align:center;"><?=jkLabel($p['jenis_kelamin']??'')?></td>
          <td style="text-align:center;font-weight:700;color:<?=$p['nilai_pip']!==null?'#1d4ed8':'#d1d5db'?>;"><?=n($p['nilai_pip'])?></td>
          <td style="text-align:center;font-weight:700;color:<?=$p['nilai_tiu']!==null?'#1d4ed8':'#d1d5db'?>;"><?=n($p['nilai_tiu'])?></td>
          <td style="text-align:center;"><?=n($p['tinggi_badan'])?></td>
          <td style="text-align:center;"><?=n($p['berat_badan'])?></td>
          <td style="text-align:center;">
            <?php if($p['kesehatan']===null): ?><span style="color:#d1d5db;">—</span>
            <?php elseif($p['kesehatan']==1): ?><span style="color:#059669;font-weight:700;font-size:12px;">✓</span>
            <?php else: ?><span style="color:#dc2626;font-weight:700;font-size:12px;">✗</span>
            <?php endif; ?>
          </td>
          <td style="text-align:center;"><?=n($p['push_up'],0)?></td>
          <td style="text-align:center;"><?=n($p['sit_up'],0)?></td>
          <td style="text-align:center;"><?=n($p['back_up'],0)?></td>
          <td style="text-align:center;"><?=n($p['lari'])?></td>
          <td style="text-align:center;"><?=n($p['pbb'])?></td>
          <td style="text-align:center;"><?=n($p['kepribadian'])?></td>
          <td style="text-align:center;"><?=tck($p['t1_done'])?></td>
          <td style="text-align:center;"><?=tck($p['t2_done'])?></td>
          <td style="text-align:center;"><?=tck($p['t3_done'])?></td>
          <td style="text-align:center;">
            <div style="display:flex;gap:3px;justify-content:center;">
              <a href="nilai.php?peserta=<?=$p['id']?>" class="btn btn-sm btn-primary btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <?php if($hasAny): ?>
              <button type="button" class="btn btn-sm btn-danger btn-icon" title="Hapus"
                      onclick="hapusNilai(<?=$p['id']?>,'<?=addslashes(htmlspecialchars($p['nama']))?>')">
                <i class="fas fa-trash"></i>
              </button>
              <?php else: ?>
              <button class="btn btn-sm btn-icon" disabled style="background:#f3f4f6;color:#d1d5db;cursor:not-allowed;"><i class="fas fa-trash"></i></button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Tab switcher
function switchTab(n) {
  const colors={1:'#1d4ed8',2:'#059669',3:'#7c3aed'};
  [1,2,3].forEach(i=>{
    const p=document.getElementById('tabPanel'+i);
    const b=document.getElementById('tabBtn'+i);
    if(p) p.style.display=(i===n)?'block':'none';
    if(b){
      b.style.color=(i===n)?colors[i]:'#6b7280';
      b.style.background=(i===n)?'#fff':'transparent';
      b.style.borderBottom=(i===n)?'2px solid '+colors[i]:'2px solid transparent';
    }
  });
}
// Hapus semua nilai peserta
function hapusTahap(id, tahap, nama) {
  Swal.fire({
    icon:'warning',title:'Hapus '+tahap+'?',
    html:`Nilai <strong>${nama}</strong> untuk ${tahap} akan dihapus.<br><small style="color:#9ca3af;">Catatan: karena data disimpan bersama, semua nilai peserta ini akan dihapus.</small>`,
    showCancelButton:true,confirmButtonText:'<i class="fas fa-trash"></i> Hapus',cancelButtonText:'Batal',confirmButtonColor:'#b91c1c'
  }).then(r=>{if(r.isConfirmed){document.getElementById('hapusNilaiId').value=id;document.getElementById('formHapusNilai').submit();}});
}
</script>

<?php
$extraJs = '<script src="' . BASE_URL . '/assets/js/nilai.js"></script>';
include __DIR__ . '/../includes/layout_end.php';
?>
