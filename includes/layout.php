<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= ($pageTitle ?? 'Dashboard') . ' — ' . APP_NAME ?></title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Icons & UI -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

  <!-- App CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <?= $extraCss ?? '' ?>
  <!-- Inject PHP constants ke JS -->
  <script>
    window.BASE_URL   = '<?= BASE_URL ?>';
    window.LOGOUT_URL = '<?= BASE_URL ?>/logout.php';
    window.PENGATURAN_URL = '<?= BASE_URL ?>/admin/pengaturan.php';
  </script>
  <!-- Terapkan sidebar state SEBELUM render untuk hindari animasi flash -->
  <script>if(localStorage.getItem('sipaskibraka_sidebar_collapsed')==='1'){document.documentElement.classList.add('sidebar-pre-collapsed');}</script>
  <style>.sidebar-pre-collapsed #sidebar,.sidebar-pre-collapsed #main-content{transition:none!important;}</style>
</head>
<body>

<!-- ── Sidebar ── -->
<div id="sidebar-overlay"></div>
<aside id="sidebar">

  <!-- Logo -->
  <div class="sidebar-logo-wrap" style="padding:20px 20px 16px; border-bottom:1px solid rgba(255,255,255,.1); display:flex; align-items:center; gap:10px;">
    <div style="width:36px;height:36px;border-radius:10px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 2px 8px rgba(0,0,0,.3);flex-shrink:0;">
      <div style="width:100%;height:50%;background:#CC0001;"></div>
      <div style="width:100%;height:50%;background:#ffffff;"></div>
    </div>
    <div class="sidebar-logo-text" style="flex:1;">
      <div style="color:#fff;font-weight:800;font-size:15px;line-height:1.1;"><?= APP_NAME ?></div>
      <div style="color:rgba(255,255,255,.5);font-size:10.5px;line-height:1.2;">Admin Panel</div>
    </div>
    <!-- Tombol close khusus mobile -->
    <button id="btn-close-sidebar" onclick="closeSidebar()"
      style="display:none;background:rgba(255,255,255,.12);border:none;cursor:pointer;
             width:30px;height:30px;border-radius:8px;color:rgba(255,255,255,.8);
             font-size:16px;align-items:center;justify-content:center;flex-shrink:0;
             transition:background .15s;"
      onmouseover="this.style.background='rgba(255,255,255,.22)'"
      onmouseout="this.style.background='rgba(255,255,255,.12)'"
      title="Tutup Sidebar">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <!-- Nav Menu -->
  <nav style="flex:1;padding:10px 0;overflow-y:auto;overflow-x:hidden;scrollbar-width:none;">

    <?php if (isSuperAdmin()): ?>
    <!-- Super Admin: menu Kelola Akun Admin + Log Aktivitas -->
    <div class="nav-section">Super Admin</div>
    <a href="<?= BASE_URL ?>/admin/super_admin.php" class="nav-item <?= ($_page??'')==='super_admin'?'active':'' ?>" data-label="Kelola Akun Admin">
      <i class="fas fa-user-shield nav-icon"></i> <span class="nav-label">Kelola Akun Admin</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/log.php" class="nav-item <?= ($_page??'')==='log'?'active':'' ?>" data-label="Log Aktivitas">
      <i class="fas fa-history nav-icon"></i> <span class="nav-label">Log Aktivitas</span>
    </a>

    <?php else: ?>
    <!-- Admin biasa: menu lengkap -->
    <div class="nav-section">Utama</div>
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-item <?= ($_page??'')==='dashboard'?'active':'' ?>" data-label="Dashboard">
      <i class="fas fa-chart-pie nav-icon"></i> <span class="nav-label">Dashboard</span>
    </a>

    <div class="nav-section">Data Peserta</div>
    <a href="<?= BASE_URL ?>/admin/peserta.php" class="nav-item <?= ($_page??'')==='peserta'?'active':'' ?>" data-label="Data Peserta">
      <i class="fas fa-users nav-icon"></i> <span class="nav-label">Data Peserta</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/nilai.php" class="nav-item <?= ($_page??'')==='nilai'?'active':'' ?>" data-label="Input Nilai">
      <i class="fas fa-clipboard-list nav-icon"></i> <span class="nav-label">Input Nilai</span>
    </a>

    <div class="nav-section">KNN & Hasil</div>
    <a href="<?= BASE_URL ?>/admin/data_latih.php" class="nav-item <?= ($_page??'')==='data_latih'?'active':'' ?>" data-label="Data Latih">
      <i class="fas fa-database nav-icon"></i> <span class="nav-label">Data Latih</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/proses_knn.php" class="nav-item <?= ($_page??'')==='proses_knn'?'active':'' ?>" data-label="Proses KNN">
      <i class="fas fa-brain nav-icon"></i> <span class="nav-label">Proses KNN</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/laporan.php" class="nav-item <?= ($_page??'')==='laporan'?'active':'' ?>" data-label="Laporan Akhir">
      <i class="fas fa-file-alt nav-icon"></i> <span class="nav-label">Laporan Akhir</span>
    </a>

    <div class="nav-section">Sistem</div>
    <a href="<?= BASE_URL ?>/admin/pengaturan.php" class="nav-item <?= ($_page??'')==='pengaturan'?'active':'' ?>" data-label="Pengaturan">
      <i class="fas fa-cog nav-icon"></i> <span class="nav-label">Pengaturan</span>
    </a>
    <?php endif; ?>

  </nav>

  <!-- User Info -->
  <div style="border-top:1px solid rgba(255,255,255,.1);">
    <div class="sidebar-user-wrap" style="display:flex;align-items:center;gap:10px;padding:14px 16px;">
      <div style="width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">
        <?= strtoupper(mb_substr($_SESSION['nama']??'A',0,1)) ?>
      </div>
      <div class="sidebar-user-name" style="flex:1;min-width:0;">
        <div style="color:#fff;font-weight:600;font-size:12.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($_SESSION['nama']??'Admin') ?></div>
        <div class="sidebar-user-role" style="color:rgba(255,255,255,.5);font-size:10.5px;"><?= isSuperAdmin() ? '👑 Super Admin' : 'Administrator' ?></div>
      </div>
      <a href="javascript:void(0)" onclick="konfirmasiLogout()" title="Logout" class="sidebar-user-logout"
         style="color:rgba(255,255,255,.5);font-size:14px;transition:color .15s;" onmouseover="this.style.color='#fca5a5'" onmouseout="this.style.color='rgba(255,255,255,.5)'">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </div>
  </div>

</aside>

<!-- ── Main Wrapper ── -->
<div id="main-content">

  <!-- Topbar -->
  <header id="topbar">
    <div style="display:flex;align-items:center;gap:14px;">
      <button id="btn-menu"
        style="background:none;border:none;cursor:pointer;padding:7px 8px;border-radius:9px;color:#6b7280;font-size:17px;display:flex;align-items:center;transition:background .15s,color .15s;"
        onmouseover="this.style.background='#f3f4f6';this.style.color='#9f1239'"
        onmouseout="this.style.background='none';this.style.color='#6b7280'"
        title="Toggle Sidebar">
        <i class="fas fa-bars"></i>
      </button>
      <div>
        <h1 style="font-size:14.5px;font-weight:800;color:#111827;margin:0;letter-spacing:-.01em;"><?= $pageTitle ?? 'Dashboard' ?></h1>
        <?php if (!empty($pageSubtitle)): ?>
        <p style="font-size:11.5px;color:#9ca3af;margin:1px 0 0;"><?= $pageSubtitle ?></p>
        <?php endif; ?>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <div class="tahun-badge" style="font-size:11.5px;font-weight:700;color:#9f1239;background:linear-gradient(135deg,#fef2f2,#fff1f2);padding:5px 12px;border-radius:20px;border:1px solid #fecaca;display:flex;align-items:center;gap:5px;">
        <i class="fas fa-calendar-alt"></i><?= getSetting('tahun_seleksi',date('Y')) ?>
      </div>
      <div style="width:1px;height:24px;background:#e5e7eb;"></div>
      <button onclick="konfirmasiLogout()" class="no-print"
        style="background:none;border:1.5px solid #e5e7eb;border-radius:9px;padding:7px 12px;cursor:pointer;color:#6b7280;font-size:13px;display:flex;align-items:center;gap:6px;font-weight:600;transition:all .15s;"
        onmouseover="this.style.borderColor='#fca5a5';this.style.color='#9f1239';this.style.background='#fef2f2'"
        onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#6b7280';this.style.background='none'"
        title="Logout">
        <i class="fas fa-sign-out-alt"></i>
        <span style="font-size:12px;">Keluar</span>
      </button>
    </div>
  </header>

  <!-- Flash Message -->
  <?php
  $flash = getFlash();
  if ($flash['msg']):
    $alertClass = match($flash['type']) {
      'error','danger' => 'alert-danger',
      'warning'        => 'alert-warning',
      'info'           => 'alert-info',
      default          => 'alert-success',
    };
    $alertIcon = match($flash['type']) {
      'error','danger' => 'fa-circle-exclamation',
      'warning'        => 'fa-triangle-exclamation',
      'info'           => 'fa-circle-info',
      default          => 'fa-circle-check',
    };
  ?>
  <div class="alert <?= $alertClass ?> no-print" data-dismiss="5000"
       style="margin:20px 28px 0;border-radius:var(--radius);animation:slideDown .3s ease;">
    <i class="fas <?= $alertIcon ?>" style="flex-shrink:0;margin-top:1px;font-size:15px;"></i>
    <span><?= $flash['msg'] ?></span>
  </div>
  <style>@keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}</style>
  <?php endif; ?>

  <!-- Page Content -->
  <main>
