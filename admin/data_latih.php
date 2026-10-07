<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
requireNotSuperAdmin();

$pdo   = getDB();
$_page = 'data_latih';
$pageTitle    = 'Data Latih KNN';
$pageSubtitle = 'Kelola data referensi untuk algoritma KNN';
$csrf = generateCSRF();

// ── POST HANDLER ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        flash('Token tidak valid.', 'error'); header('Location: data_latih.php'); exit;
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah' || $action === 'edit') {
        $id    = (int)($_POST['id'] ?? 0);
        $nama  = sanitize($_POST['nama_referensi'] ?? '');
        $label = in_array($_POST['label']??'', ['Lulus','Tidak Lulus']) ? $_POST['label'] : 'Lulus';
        $fields = ['nilai_pip','nilai_tiu','tinggi_badan','berat_badan','kesehatan',
                   'push_up','sit_up','back_up','lari','pbb','kepribadian'];
        $vals = [];
        $err  = [];
        if (!$nama) $err[] = 'Nama referensi wajib diisi.';
        foreach ($fields as $f) {
            $v = floatval($_POST[$f] ?? 0);
            if ($f === 'kesehatan' && !in_array((int)$v,[0,1])) { $err[] = "Kesehatan harus 0 atau 1."; }
            $vals[$f] = $v;
        }
        if (empty($err)) {
            $cols = implode(',', array_merge(['nama_referensi','label'], $fields));
            $phs  = implode(',', array_fill(0, count($fields)+2, '?'));
            $data = array_merge([$nama,$label], array_values($vals));
            if ($action === 'tambah') {
                $pdo->prepare("INSERT INTO data_latih ($cols) VALUES ($phs)")->execute($data);
                flash("Data latih '$nama' berhasil ditambahkan.");
                catatLog('TAMBAH_DATA_LATIH', "Nama=$nama Label=$label");
            } else {
                $sets = implode(',', array_map(fn($f) => "$f=?", array_merge(['nama_referensi','label'], $fields)));
                $pdo->prepare("UPDATE data_latih SET $sets WHERE id=?")->execute(array_merge($data, [$id]));
                flash("Data latih '$nama' berhasil diperbarui.");
                catatLog('EDIT_DATA_LATIH', "ID=$id Nama=$nama");
            }
        } else { flash(implode(' ', $err), 'error'); }
        header('Location: data_latih.php'); exit;

    } elseif ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $r  = $pdo->prepare("SELECT nama_referensi FROM data_latih WHERE id=?");
        $r->execute([$id]);
        $row = $r->fetch();
        if ($row) {
            $pdo->prepare("DELETE FROM data_latih WHERE id=?")->execute([$id]);
            catatLog('HAPUS_DATA_LATIH', "ID=$id Nama={$row['nama_referensi']}");
            flash("Data latih '{$row['nama_referensi']}' dihapus.");
        }
        header('Location: data_latih.php'); exit;

    } elseif ($action === 'import_csv') {
        if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
            flash('File gagal diupload.', 'error'); header('Location: data_latih.php'); exit;
        }
        $tmp = $_FILES['file_csv']['tmp_name'];
        $ok  = 0; $skip = 0;
        $fields = ['nama_referensi','label','nilai_pip','nilai_tiu','tinggi_badan','berat_badan',
                   'kesehatan','push_up','sit_up','back_up','lari','pbb','kepribadian'];
        if (($fh = fopen($tmp,'r')) !== false) {
            fgetcsv($fh); // skip header
            while (($row = fgetcsv($fh)) !== false) {
                if (count($row) < 13) { $skip++; continue; }
                $label = in_array(trim($row[1]),['Lulus','Tidak Lulus']) ? trim($row[1]) : 'Lulus';
                $pdo->prepare("INSERT INTO data_latih (nama_referensi,label,nilai_pip,nilai_tiu,tinggi_badan,berat_badan,kesehatan,push_up,sit_up,back_up,lari,pbb,kepribadian) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
                    ->execute([trim($row[0]),$label,floatval($row[2]),floatval($row[3]),floatval($row[4]),floatval($row[5]),(int)$row[6],floatval($row[7]),floatval($row[8]),floatval($row[9]),floatval($row[10]),floatval($row[11]),floatval($row[12])]);
                $ok++;
            }
            fclose($fh);
        }
        catatLog('IMPORT_DATA_LATIH', "OK=$ok Skip=$skip");
        flash("Import selesai: $ok data ditambahkan, $skip dilewati.");
        header('Location: data_latih.php'); exit;
    }
}

// ── GET DATA ──────────────────────────────────────────────
$dataLatih = $pdo->query("SELECT * FROM data_latih ORDER BY id DESC")->fetchAll();
$totalLulus = count(array_filter($dataLatih, fn($r) => $r['label']==='Lulus'));
$totalTidak = count($dataLatih) - $totalLulus;

include __DIR__ . '/../includes/layout.php';
?>

<div class="page-header">
  <div>
    <h2 class="page-title"><span class="icon"><i class="fas fa-database"></i></span>Data Latih KNN</h2>
    <p class="page-subtitle">Kelola data referensi untuk algoritma KNN</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <button class="btn btn-secondary btn-sm" onclick="openModal('modalImport')"><i class="fas fa-file-csv"></i> Import CSV</button>
    <button class="btn btn-secondary btn-sm" onclick="downloadTemplate()"><i class="fas fa-download"></i> Template</button>
    <button class="btn btn-primary" onclick="openModal('modalForm')"><i class="fas fa-plus"></i> Tambah Data</button>
  </div>
</div>

<!-- Statistik Data Latih -->
<div class="stat-grid">
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-database" style="color:#3b82f6;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#111827;line-height:1;"><?= count($dataLatih) ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Total Data Latih</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-circle-check" style="color:#10b981;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#10b981;line-height:1;"><?= $totalLulus ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Label Lulus</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-circle-xmark" style="color:#ef4444;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#ef4444;line-height:1;"><?= $totalTidak ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Label Tidak Lulus</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:42px;height:42px;border-radius:11px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-scale-balanced" style="color:#f59e0b;font-size:17px;"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:#f59e0b;line-height:1;"><?= count($dataLatih) > 0 ? round($totalLulus/count($dataLatih)*100) : 0 ?>%</div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;">Rasio Lulus</div>
    </div>
  </div>
</div>

<!-- Info -->
<div class="alert alert-info" style="margin-bottom:20px;">
  <i class="fas fa-info-circle"></i>
  <div>Data latih adalah data historis yang digunakan KNN sebagai acuan klasifikasi. Semakin banyak dan representatif, semakin akurat hasil KNN.
  Kolom <strong>kesehatan</strong>: 1 = Sehat, 0 = Tidak Sehat.</div>
</div>

<div class="card" style="padding:24px;">
  <div class="table-wrap">
    <table class="data-table" id="tblDataLatih">
      <thead>
        <tr>
          <th>#</th><th>Nama Referensi</th><th>Label</th>
          <th style="text-align:center;">PIP</th><th style="text-align:center;">TIU</th>
          <th style="text-align:center;">TB</th><th style="text-align:center;">BB</th>
          <th style="text-align:center;">Kes.</th><th style="text-align:center;">Push</th>
          <th style="text-align:center;">Sit</th><th style="text-align:center;">Back</th>
          <th style="text-align:center;">Lari</th><th style="text-align:center;">PBB</th>
          <th style="text-align:center;">Kep.</th>
          <th style="text-align:center;width:90px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dataLatih as $i => $d): ?>
        <tr>
          <td style="color:#9ca3af;font-size:12px;"><?= $i+1 ?></td>
          <td style="font-weight:600;color:#111827;"><?= htmlspecialchars($d['nama_referensi']) ?></td>
          <td><?= $d['label']==='Lulus'
            ? '<span class="badge badge-success"><i class="fas fa-check"></i> Lulus</span>'
            : '<span class="badge badge-danger"><i class="fas fa-times"></i> Tidak Lulus</span>' ?></td>
          <?php foreach(['nilai_pip','nilai_tiu','tinggi_badan','berat_badan','kesehatan','push_up','sit_up','back_up','lari','pbb','kepribadian'] as $f): ?>
          <td style="text-align:center;font-size:12.5px;color:#374151;"><?= $d[$f] ?></td>
          <?php endforeach; ?>
          <td style="text-align:center;">
            <div style="display:flex;gap:4px;justify-content:center;">
              <button class="btn btn-sm btn-secondary btn-icon" onclick='editData(<?= json_encode($d) ?>)' title="Edit"><i class="fas fa-pencil"></i></button>
              <button class="btn btn-sm btn-danger btn-icon" onclick="hapusData(<?= $d['id'] ?>, '<?= addslashes(htmlspecialchars($d['nama_referensi'])) ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Form Tambah/Edit -->
<div class="modal-backdrop" id="modalForm">
  <div class="modal modal-xl">
    <div class="modal-header">
      <h4 class="modal-title" id="modalFormTitle">Tambah Data Latih</h4>
      <button class="modal-close" onclick="closeModal('modalForm')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" id="formDataLatih">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" id="fAction" value="tambah">
        <input type="hidden" name="id" id="fId" value="">
        <div class="form-row form-row-2" style="margin-bottom:4px;">
          <div class="form-group">
            <label class="form-label">Nama Referensi <span class="required">*</span></label>
            <input type="text" name="nama_referensi" id="fNama" class="form-control" placeholder="Nama data referensi" required>
          </div>
          <div class="form-group">
            <label class="form-label">Label Klasifikasi <span class="required">*</span></label>
            <select name="label" id="fLabel" class="form-control form-select" required>
              <option value="Lulus">Lulus</option>
              <option value="Tidak Lulus">Tidak Lulus</option>
            </select>
          </div>
        </div>
        <div class="divider" style="margin:8px 0;"></div>
        <p style="font-size:12px;font-weight:600;color:#6b7280;margin-bottom:12px;">DATA NILAI</p>
        <div class="form-row form-row-4">
          <div class="form-group"><label class="form-label">PIP (0–100)</label><input type="number" name="nilai_pip" id="fPIP" class="form-control" min="0" max="100" step="0.01" value="0" required></div>
          <div class="form-group"><label class="form-label">TIU (0–100)</label><input type="number" name="nilai_tiu" id="fTIU" class="form-control" min="0" max="100" step="0.01" value="0" required></div>
          <div class="form-group"><label class="form-label">Tinggi Badan (cm)</label><input type="number" name="tinggi_badan" id="fTB" class="form-control" min="100" max="250" step="0.1" value="0" required></div>
          <div class="form-group"><label class="form-label">Berat Badan (kg)</label><input type="number" name="berat_badan" id="fBB" class="form-control" min="20" max="200" step="0.1" value="0" required></div>
        </div>
        <div class="form-row form-row-4">
          <div class="form-group">
            <label class="form-label">Kesehatan</label>
            <select name="kesehatan" id="fKes" class="form-control form-select" required>
              <option value="1">1 — Sehat</option>
              <option value="0">0 — Tidak Sehat</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Push Up</label><input type="number" name="push_up" id="fPU" class="form-control" min="0" max="500" step="1" value="0" required></div>
          <div class="form-group"><label class="form-label">Sit Up</label><input type="number" name="sit_up" id="fSU" class="form-control" min="0" max="500" step="1" value="0" required></div>
          <div class="form-group"><label class="form-label">Back Up</label><input type="number" name="back_up" id="fBU" class="form-control" min="0" max="500" step="1" value="0" required></div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group"><label class="form-label">Lari (putaran)</label><input type="number" name="lari" id="fLR" class="form-control" min="0" max="15" step="0.5" value="0" required></div>
          <div class="form-group"><label class="form-label">PBB (0–100)</label><input type="number" name="pbb" id="fPB" class="form-control" min="0" max="100" step="0.01" value="0" required></div>
          <div class="form-group"><label class="form-label">Kepribadian (0–100)</label><input type="number" name="kepribadian" id="fKEP" class="form-control" min="0" max="100" step="0.01" value="0" required></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalForm')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Import -->
<div class="modal-backdrop" id="modalImport">
  <div class="modal">
    <div class="modal-header">
      <h4 class="modal-title">Import Data Latih (CSV)</h4>
      <button class="modal-close" onclick="closeModal('modalImport');clearImportFile()"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data" id="formImportLatih">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="import_csv">
        <input type="file" name="file_csv" id="fileCsvInput" accept=".csv,.xlsx,.xls" style="display:none;" required>

        <!-- Info format -->
        <div class="alert alert-info" style="margin-bottom:16px;">
          <i class="fas fa-info-circle"></i>
          <div>Format: <strong>Nama Referensi, Label, PIP, TIU, TB, BB, Kes, Push, Sit, Back, Lari, PBB, Kepribadian</strong><br>
          Label: "Lulus" atau "Tidak Lulus". Kesehatan: 1=Sehat, 0=Tidak.</div>
        </div>

        <!-- Drop Zone -->
        <div id="dropZoneLatih"
             onclick="document.getElementById('fileCsvInput').click()"
             ondragover="event.preventDefault();this.classList.add('dz-over')"
             ondragleave="this.classList.remove('dz-over')"
             ondrop="handleDropLatih(event)"
             style="border:2px dashed #6ee7b7;background:#f0fdf4;border-radius:14px;padding:22px 20px;display:flex;align-items:center;gap:16px;cursor:pointer;transition:all .2s;margin-bottom:0;">
          <div id="dzLatihIcon" style="width:48px;height:48px;border-radius:12px;background:#059669;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-cloud-upload-alt" style="color:#fff;font-size:20px;"></i>
          </div>
          <div id="dzLatihText" style="flex:1;">
            <p style="font-size:13.5px;font-weight:700;color:#065f46;margin:0 0 2px;">Klik atau seret file ke sini</p>
            <p style="font-size:12px;color:#6b7280;margin:0;">Format: .csv, .xlsx, .xls — Maks 5 MB</p>
          </div>
          <div id="dzLatihClear" style="display:none;">
            <button type="button" onclick="event.stopPropagation();clearImportFile()"
                    style="border:none;background:#fef2f2;color:#dc2626;border-radius:8px;padding:5px 10px;cursor:pointer;font-size:12px;">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalImport');clearImportFile()">Batal</button>
        <button type="submit" id="btnImportLatih" class="btn btn-primary" disabled style="opacity:.5;cursor:not-allowed;">
          <i class="fas fa-upload"></i> Import
        </button>
      </div>
    </form>
  </div>
</div>

<style>
#dropZoneLatih.dz-over    { border-color:#059669; background:#dcfce7; transform:scale(1.01); }
#dropZoneLatih.dz-has-file{ border-color:#059669; border-style:solid; background:#f0fdf4; }
</style>

<script>
document.getElementById('fileCsvInput').addEventListener('change', function(){
  setImportFile(this.files[0]);
});

function handleDropLatih(e) {
  e.preventDefault();
  document.getElementById('dropZoneLatih').classList.remove('dz-over');
  const file = e.dataTransfer.files[0];
  if (!file) return;
  const ext = file.name.split('.').pop().toLowerCase();
  if (!['csv','xlsx','xls'].includes(ext)) {
    alert('Format tidak didukung. Gunakan .csv, .xlsx, atau .xls');
    return;
  }
  const dt = new DataTransfer();
  dt.items.add(file);
  document.getElementById('fileCsvInput').files = dt.files;
  setImportFile(file);
}

function setImportFile(file) {
  if (!file) return;
  const dz   = document.getElementById('dropZoneLatih');
  const icon = document.getElementById('dzLatihIcon');
  const text = document.getElementById('dzLatihText');
  const clr  = document.getElementById('dzLatihClear');
  const btn  = document.getElementById('btnImportLatih');

  dz.classList.add('dz-has-file');
  icon.innerHTML = '<i class="fas fa-file-csv" style="color:#fff;font-size:20px;"></i>';
  text.innerHTML = `<p style="font-size:13.5px;font-weight:700;color:#065f46;margin:0 0 2px;">${file.name}</p>
    <p style="font-size:12px;color:#6b7280;margin:0;">${(file.size/1024).toFixed(1)} KB &nbsp;·&nbsp; Siap diimport</p>`;
  clr.style.display = 'block';
  btn.disabled = false;
  btn.style.opacity = '1';
  btn.style.cursor = 'pointer';
}

function clearImportFile() {
  const dz   = document.getElementById('dropZoneLatih');
  const icon = document.getElementById('dzLatihIcon');
  const text = document.getElementById('dzLatihText');
  const clr  = document.getElementById('dzLatihClear');
  const btn  = document.getElementById('btnImportLatih');

  document.getElementById('fileCsvInput').value = '';
  dz.classList.remove('dz-has-file','dz-over');
  icon.innerHTML = '<i class="fas fa-cloud-upload-alt" style="color:#fff;font-size:20px;"></i>';
  text.innerHTML = `<p style="font-size:13.5px;font-weight:700;color:#065f46;margin:0 0 2px;">Klik atau seret file ke sini</p>
    <p style="font-size:12px;color:#6b7280;margin:0;">Format: .csv, .xlsx, .xls — Maks 5 MB</p>`;
  clr.style.display = 'none';
  btn.disabled = true;
  btn.style.opacity = '.5';
  btn.style.cursor = 'not-allowed';
}
</script>

<!-- Form Hapus -->
<form id="formHapus" method="POST" style="display:none;">
  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
  <input type="hidden" name="action" value="hapus">
  <input type="hidden" name="id" id="hapusId">
</form>

<?php
$extraJs = '<script src="' . BASE_URL . '/assets/js/data_latih.js"></script>';
include __DIR__ . '/../includes/layout_end.php';
?>
