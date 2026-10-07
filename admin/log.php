<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireSuperAdmin();

$pdo   = getDB();
$_page = 'log';
$pageTitle    = 'Log Aktivitas';
$pageSubtitle = 'Rekam jejak semua aktivitas admin';
$csrf  = generateCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRF($_POST['csrf_token']??'')) {
    if ($_POST['action'] === 'hapus_semua') {
        $pdo->exec("DELETE FROM log_aktivitas");
        catatLog('HAPUS_LOG', 'Semua log dihapus');
        flash('Semua log berhasil dihapus.', 'warning');
    }
    header('Location: log.php'); exit;
}

$logs = $pdo->query("SELECT * FROM log_aktivitas ORDER BY created_at DESC LIMIT 500")->fetchAll();

// Hitung statistik
$totalLog    = count($logs);
$totalLogin  = count(array_filter($logs, fn($l) => str_contains($l['aksi'], 'LOGIN')));
$totalLogout = count(array_filter($logs, fn($l) => str_contains($l['aksi'], 'LOGOUT')));
$totalKNN    = count(array_filter($logs, fn($l) => str_contains($l['aksi'], 'KNN')));
$totalHapus  = count(array_filter($logs, fn($l) => str_contains($l['aksi'], 'HAPUS')));
$totalPeserta= count(array_filter($logs, fn($l) => str_contains($l['aksi'], 'PESERTA')));
$totalLainnya= $totalLog - $totalLogin - $totalLogout - $totalKNN - $totalHapus - $totalPeserta;
$totalLainnya= max(0, $totalLainnya);

// Mapping badge style per aksi
function badgeLog(string $aksi): array {
    if (str_contains($aksi,'HAPUS'))   return ['danger',  '#dc2626','#fef2f2'];
    if (str_contains($aksi,'KNN'))     return ['success', '#059669','#f0fdf4'];
    if (str_contains($aksi,'LOGIN'))   return ['blue',    '#2563eb','#eff6ff'];
    if (str_contains($aksi,'LOGOUT'))  return ['gray',    '#6b7280','#f3f4f6'];
    if (str_contains($aksi,'PESERTA')) return ['warning', '#d97706','#fffbeb'];
    if (str_contains($aksi,'NILAI'))   return ['purple',  '#7c3aed','#f5f3ff'];
    if (str_contains($aksi,'LATIH'))   return ['info',    '#0284c7','#e0f2fe'];
    if (str_contains($aksi,'SUPER'))   return ['danger',  '#9f1239','#fef2f2'];
    return ['gray', '#6b7280','#f3f4f6'];
}

function rowBg(string $aksi): string {
    if (str_contains($aksi,'HAPUS'))   return '#fff5f5';
    if (str_contains($aksi,'KNN'))     return '#f6fef9';
    if (str_contains($aksi,'LOGIN'))   return '#f0f6ff';
    if (str_contains($aksi,'PESERTA')) return '#fffdf0';
    if (str_contains($aksi,'NILAI'))   return '#faf5ff';
    return '';
}

include __DIR__ . '/../includes/layout.php';
?>

<div class="page-header">
  <div>
    <h2 class="page-title"><span class="icon"><i class="fas fa-history"></i></span>Log Aktivitas</h2>
    <p class="page-subtitle">Rekam jejak semua aktivitas admin sistem</p>
  </div>
  <form method="POST" id="formHapusLog">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="action" value="hapus_semua">
    <button type="button" class="btn btn-danger btn-sm"
            onclick="konfirmasiHapusLog(document.getElementById('formHapusLog'))">
      <i class="fas fa-trash"></i> Hapus Semua
    </button>
  </form>
</div>

<!-- ── Stat Cards ─────────────────────────────────────────── -->
<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(130px,1fr));margin-bottom:20px;">
  <?php
  $statItems = [
    ['fas fa-list',        '#3b82f6','#eff6ff', $totalLog,     'Total Log'],
    ['fas fa-sign-in-alt', '#2563eb','#dbeafe', $totalLogin,   'Login'],
    ['fas fa-sign-out-alt','#6b7280','#f3f4f6', $totalLogout,  'Logout'],
    ['fas fa-brain',       '#059669','#dcfce7', $totalKNN,     'Proses KNN'],
    ['fas fa-users',       '#d97706','#fef3c7', $totalPeserta, 'Peserta'],
    ['fas fa-trash',       '#dc2626','#fee2e2', $totalHapus,   'Hapus Data'],
    ['fas fa-ellipsis-h',  '#9ca3af','#f9fafb', $totalLainnya, 'Lainnya'],
  ];
  foreach ($statItems as [$ico,$clr,$bg,$val,$lbl]): ?>
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px 16px;
              display:flex;align-items:center;gap:12px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
    <div style="width:38px;height:38px;border-radius:10px;background:<?= $bg ?>;
                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="<?= $ico ?>" style="color:<?= $clr ?>;font-size:15px;"></i>
    </div>
    <div>
      <div style="font-size:20px;font-weight:800;color:#111827;line-height:1;"><?= $val ?></div>
      <div style="font-size:11px;color:#6b7280;margin-top:2px;"><?= $lbl ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Filter Buttons ─────────────────────────────────────── -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center;">
  <span style="font-size:11.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-right:4px;">
    <i class="fas fa-filter" style="margin-right:4px;"></i>Filter:
  </span>
  <?php
  $filters = [
    ['Semua',   '',         'btn-secondary', 'fas fa-list'],
    ['Login',   'LOGIN',    'btn-info',      'fas fa-sign-in-alt'],
    ['Logout',  'LOGOUT',   '',              'fas fa-sign-out-alt'],
    ['KNN',     'KNN',      'btn-success',   'fas fa-brain'],
    ['Peserta', 'PESERTA',  'btn-warning',   'fas fa-users'],
    ['Nilai',   'NILAI',    '',              'fas fa-clipboard-list'],
    ['Hapus',   'HAPUS',    'btn-danger',    'fas fa-trash'],
    ['Super Admin','SUPER', '',              'fas fa-user-shield'],
  ];
  foreach ($filters as [$label, $key, $cls, $ico]): ?>
  <button class="btn btn-sm <?= $cls ?: 'btn-secondary' ?> filter-btn"
          data-filter="<?= $key ?>"
          style="<?= !$cls ? 'background:#fff;border:1px solid #e5e7eb;color:#374151;' : '' ?>">
    <i class="<?= $ico ?>"></i> <?= $label ?>
    <?php if ($key): ?>
    <span class="badge" style="background:rgba(255,255,255,.25);color:inherit;font-size:10px;padding:1px 6px;margin-left:2px;">
      <?= count(array_filter($logs, fn($l) => str_contains($l['aksi'], $key))) ?>
    </span>
    <?php endif; ?>
  </button>
  <?php endforeach; ?>
</div>

<!-- ── Tabel Log ──────────────────────────────────────────── -->
<div class="card" style="padding:24px;">
  <div class="table-wrap">
    <table class="data-table" id="tblLog">
      <thead>
        <tr>
          <th style="width:140px;">Waktu</th>
          <th style="width:160px;">Admin</th>
          <th style="width:150px;">Aksi</th>
          <th>Detail</th>
          <th style="width:90px;">IP</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $l):
          [$badgeClass] = badgeLog($l['aksi']);
          $bg = rowBg($l['aksi']);
        ?>
        <tr class="log-row" data-aksi="<?= htmlspecialchars($l['aksi']) ?>"
            style="background:<?= $bg ?>;">
          <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
            <i class="fas fa-clock" style="color:#d1d5db;margin-right:4px;font-size:10px;"></i>
            <?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?>
          </td>
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#9f1239,#7f1d1d);
                          display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="color:#fff;font-size:10px;font-weight:700;">
                  <?= strtoupper(substr($l['nama_admin']??'?', 0, 1)) ?>
                </span>
              </div>
              <span style="font-weight:600;font-size:12.5px;"><?= htmlspecialchars($l['nama_admin']??'—') ?></span>
            </div>
          </td>
          <td>
            <?php [$cls,$clr,$bg2] = badgeLog($l['aksi']); ?>
            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
                         border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.02em;
                         background:<?= $bg2 ?>;color:<?= $clr ?>;border:1px solid <?= $clr ?>22;">
              <?= htmlspecialchars($l['aksi']) ?>
            </span>
          </td>
          <td style="font-size:12px;color:#374151;"><?= htmlspecialchars($l['detail']??'') ?></td>
          <td style="font-size:11px;color:#9ca3af;font-family:monospace;"><?= htmlspecialchars($l['ip_address']??'') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$extraJs = '<script>
$(document).ready(function(){
  initDataTable("tblLog", {order:[[0,"desc"]], columnDefs:[{orderable:false,targets:4}]});

  // Filter buttons
  $(".filter-btn").on("click", function(){
    $(".filter-btn").removeClass("active-filter").css("opacity","1");
    $(this).addClass("active-filter");
    var key = $(this).data("filter");
    var table = $("#tblLog").DataTable();
    if (!key) {
      table.column(2).search("").draw();
    } else {
      table.column(2).search(key, false, false).draw();
    }
  });
  // Highlight active filter
  $(".filter-btn").first().addClass("active-filter");
});

function konfirmasiHapusLog(form) {
  Swal.fire({
    html: `<div style="text-align:center;padding:6px 0 2px;">
      <div style="width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(220,38,38,.2);">
        <i class="fas fa-list-alt" style="font-size:22px;color:#dc2626;"></i>
      </div>
      <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:\'Plus Jakarta Sans\',sans-serif;">Hapus Semua Log?</h3>
      <p style="font-size:13.5px;color:#9b8b8b;margin:0;line-height:1.6;">Seluruh riwayat aktivitas akan dihapus permanen dan tidak bisa dipulihkan.</p>
    </div>`,
    showCancelButton: true,
    confirmButtonText: \'<i class="fas fa-trash-alt"></i>&nbsp;Ya, Hapus Log\',
    cancelButtonText: \'<i class="fas fa-times"></i>&nbsp;Batal\',
    confirmButtonColor: "#dc2626", cancelButtonColor: "#f3f4f6",
    width: 380, padding: "28px 28px 24px", borderRadius: "20px",
    backdrop: "rgba(15,10,10,0.55)",
    customClass: { popup: "swal-popup-custom", cancelButton: "swal-cancel-grey" },
  }).then(r => { if (r.isConfirmed) form.submit(); });
}
</script>';
include __DIR__ . '/../includes/layout_end.php';
?>
