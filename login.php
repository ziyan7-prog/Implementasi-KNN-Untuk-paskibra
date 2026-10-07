<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

if (isLoggedIn()) {
    $dest = isSuperAdmin() ? BASE_URL . '/admin/super_admin.php' : BASE_URL . '/admin/dashboard.php';
    header('Location: ' . $dest); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid. Muat ulang halaman.';
    } else {
        $result = loginAdmin(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
        if ($result['ok']) {
            $dest = isSuperAdmin() ? BASE_URL . '/admin/super_admin.php' : BASE_URL . '/admin/dashboard.php';
            $redirect = $_GET['redirect'] ?? $dest;
            // Jangan biarkan superadmin redirect ke halaman lain
            if (isSuperAdmin()) { $redirect = $dest; }
            header('Location: ' . $redirect); exit;
        }
        $error = $result['msg'];
    }
}
$csrf = generateCSRF();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --merah:   #9f1239;
      --merah2:  #be123c;
      --merah3:  #7f1d1d;
      --putih:   #ffffff;
      --gelap:   #0f0a0a;
      --text:    #1a0a0a;
      --muted:   #9b8b8b;
      --border:  #f0e8e8;
    }

    html, body { height: 100%; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      display: flex;
      min-height: 100vh;
      background: #0f0a0a;
      overflow: hidden;
    }

    /* ══ PANEL KIRI — Ilustrasi Paskibraka ══ */
    .panel-left {
      flex: 1.1;
      position: relative;
      background: linear-gradient(155deg, #7f1d1d 0%, #9f1239 35%, #4c0519 70%, #0f0a0a 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 60px 48px;
      overflow: hidden;
    }

    /* Texture overlay */
    .panel-left::before {
      content: '';
      position: absolute; inset: 0;
      background-image:
        radial-gradient(circle at 20% 20%, rgba(255,255,255,.04) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255,255,255,.03) 0%, transparent 50%);
      pointer-events: none;
    }

    /* Garis dekoratif diagonal */
    .deco-lines {
      position: absolute; inset: 0; overflow: hidden; pointer-events: none;
    }
    .deco-lines span {
      position: absolute;
      width: 1px;
      background: rgba(255,255,255,.06);
      transform-origin: top center;
    }

    /* ══ Animasi Logo Paskibraka Floating ══ */
    .paskibraka-scene {
      position: relative;
      width: 320px;
      height: 360px;
      margin-bottom: 36px;
      flex-shrink: 0;
    }

    @keyframes pulse-ring {
      0%,100% { opacity: .25; transform: scale(1); }
      50%      { opacity: .07; transform: scale(1.05); }
    }

    /* ══ Animasi Logo Paskibraka Floating ══ */
    .logo-float-wrap {
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .logo-float {
      width: 240px;
      height: 240px;
      object-fit: contain;
      filter: drop-shadow(0 0 32px rgba(255,200,50,.25)) drop-shadow(0 8px 32px rgba(0,0,0,.5));
      animation: logo-floating 4s ease-in-out infinite;
    }

    @keyframes logo-floating {
      0%,100% { transform: translateY(0px) rotate(0deg); filter: drop-shadow(0 0 28px rgba(255,200,50,.2)) drop-shadow(0 8px 28px rgba(0,0,0,.4)); }
      25%     { transform: translateY(-10px) rotate(.5deg); filter: drop-shadow(0 0 40px rgba(255,200,50,.35)) drop-shadow(0 16px 40px rgba(0,0,0,.3)); }
      75%     { transform: translateY(-6px) rotate(-.4deg); filter: drop-shadow(0 0 36px rgba(255,200,50,.3)) drop-shadow(0 12px 36px rgba(0,0,0,.35)); }
    }

    .logo-glow {
      width: 160px; height: 20px;
      background: radial-gradient(ellipse, rgba(255,200,50,.18) 0%, transparent 70%);
      border-radius: 50%;
      margin-top: 12px;
      animation: glow-pulse 4s ease-in-out infinite;
    }

    @keyframes glow-pulse {
      0%,100% { opacity: .5; transform: scaleX(1); }
      25%     { opacity: .2; transform: scaleX(.7); }
      75%     { opacity: .35; transform: scaleX(.8); }
    }

    /* Lingkaran aura di belakang */
    .aura-ring {
      position: absolute;
      border-radius: 50%;
      border: 1px solid rgba(255,200,50,.12);
      animation: pulse-ring 3.5s ease-in-out infinite;
    }
    .aura-ring:nth-child(1) { width: 290px; height: 290px; top: 35px; left: 15px; animation-delay: 0s; }
    .aura-ring:nth-child(2) { width: 230px; height: 230px; top: 65px; left: 45px; animation-delay: .6s; }
    .aura-ring:nth-child(3) { width: 170px; height: 170px; top: 95px; left: 75px; animation-delay: 1.2s; }

    /* Partikel bintang */
    .star-particle {
      position: absolute;
      width: 4px; height: 4px;
      background: #fde68a;
      border-radius: 50%;
      animation: float-star 4s ease-in-out infinite;
    }
    @keyframes float-star {
      0%,100% { opacity: 0; transform: translateY(0) scale(0); }
      20%     { opacity: 1; transform: translateY(-8px) scale(1); }
      80%     { opacity: .5; transform: translateY(-24px) scale(.6); }
    }

    /* Teks panel kiri */
    .panel-left-title {
      font-family: 'Playfair Display', serif;
      font-size: 36px;
      font-weight: 900;
      color: #fff;
      text-align: center;
      line-height: 1.15;
      margin-bottom: 14px;
      position: relative;
    }
    .panel-left-title em {
      font-style: normal;
      color: #fde68a;
    }
    .panel-left-sub {
      font-size: 13.5px;
      color: rgba(255,255,255,.55);
      text-align: center;
      max-width: 280px;
      line-height: 1.65;
      position: relative;
    }

    /* Bendera Indonesia kecil dekoratif */
    .mini-flags {
      display: flex;
      gap: 8px;
      justify-content: center;
      margin-top: 22px;
      position: relative;
    }
    .mini-flag {
      width: 32px; height: 20px;
      border-radius: 3px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,.4);
      animation: mini-flag-float 2.5s ease-in-out infinite;
    }
    .mini-flag:nth-child(2) { animation-delay: .3s; }
    .mini-flag:nth-child(3) { animation-delay: .6s; }
    .mini-flag .mf-red { width: 100%; height: 50%; background: #CC0001; }
    .mini-flag .mf-white { width: 100%; height: 50%; background: #fff; }
    @keyframes mini-flag-float {
      0%,100% { transform: translateY(0) rotate(-2deg); }
      50%     { transform: translateY(-4px) rotate(2deg); }
    }

    /* Tahun badge */
    .year-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,255,255,.1);
      border: 1px solid rgba(255,255,255,.2);
      color: rgba(255,255,255,.8);
      font-size: 11.5px;
      font-weight: 700;
      padding: 5px 14px;
      border-radius: 99px;
      margin-top: 18px;
      position: relative;
      letter-spacing: .5px;
    }

    /* ══ PANEL KANAN — Form Login ══ */
    .panel-right {
      width: 440px;
      flex-shrink: 0;
      background: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 44px;
      position: relative;
      overflow: hidden;
    }

    /* Dekorasi sudut */
    .panel-right::before {
      content: '';
      position: absolute;
      top: -60px; right: -60px;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: radial-gradient(circle, #fef2f2 0%, transparent 70%);
    }
    .panel-right::after {
      content: '';
      position: absolute;
      bottom: -40px; left: -40px;
      width: 160px; height: 160px;
      border-radius: 50%;
      background: radial-gradient(circle, #fef2f2 0%, transparent 70%);
    }

    .login-inner { width: 100%; max-width: 340px; position: relative; z-index: 1; }

    /* Logo */
    .login-logo-wrap {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 32px;
    }
    .login-flag {
      width: 46px; height: 46px;
      border-radius: 12px;
      overflow: hidden;
      display: flex; flex-direction: column;
      box-shadow: 0 4px 16px rgba(159,18,57,.35);
      flex-shrink: 0;
    }
    .login-flag .f-red   { width: 100%; height: 50%; background: #CC0001; }
    .login-flag .f-white { width: 100%; height: 50%; background: #fff; border: .5px solid #f0e8e8; }
    .login-app-name {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      font-weight: 900;
      color: var(--text);
      line-height: 1.1;
    }
    .login-app-sub {
      font-size: 11px;
      color: var(--muted);
      font-weight: 600;
      letter-spacing: .3px;
      margin-top: 2px;
    }

    /* Heading form */
    .login-heading {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      font-weight: 900;
      color: var(--text);
      margin-bottom: 6px;
    }
    .login-heading-sub {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 28px;
    }

    /* Divider */
    .login-divider {
      width: 40px; height: 3px;
      background: var(--merah);
      border-radius: 99px;
      margin-bottom: 28px;
    }

    /* Input fields */
    .field-wrap {
      position: relative;
      margin-bottom: 16px;
    }
    .field-label {
      font-size: 12px;
      font-weight: 700;
      color: #374151;
      margin-bottom: 6px;
      display: block;
      letter-spacing: .3px;
      text-transform: uppercase;
    }
    .field-input {
      width: 100%;
      padding: 12px 16px 12px 42px;
      border: 1.5px solid var(--border);
      border-radius: 12px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 14px;
      color: var(--text);
      background: #fafafa;
      transition: border-color .2s, box-shadow .2s, background .2s;
      outline: none;
    }
    .field-input:focus {
      border-color: var(--merah);
      background: #fff;
      box-shadow: 0 0 0 4px rgba(159,18,57,.08);
    }
    .field-icon {
      position: absolute;
      left: 14px;
      bottom: 13px;
      color: #c4b5b5;
      font-size: 14px;
      pointer-events: none;
      transition: color .2s;
    }
    .field-wrap:focus-within .field-icon { color: var(--merah); }
    .field-eye {
      position: absolute;
      right: 14px;
      bottom: 12px;
      background: none;
      border: none;
      cursor: pointer;
      color: #c4b5b5;
      font-size: 13px;
      padding: 2px;
      transition: color .2s;
    }
    .field-eye:hover { color: var(--merah); }

    /* Error alert */
    .err-alert {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 14px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 10px;
      color: #991b1b;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 18px;
    }
    .err-alert i { font-size: 15px; flex-shrink: 0; }

    /* Submit button */
    .btn-masuk {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, var(--merah) 0%, var(--merah3) 100%);
      color: #fff;
      border: none;
      border-radius: 12px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 14.5px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: transform .15s, box-shadow .15s, filter .15s;
      box-shadow: 0 4px 20px rgba(159,18,57,.35);
      letter-spacing: .3px;
    }
    .btn-masuk:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 28px rgba(159,18,57,.45);
      filter: brightness(1.05);
    }
    .btn-masuk:active { transform: translateY(0); }

    /* Loading spinner */
    .btn-masuk .spinner {
      display: none;
      width: 16px; height: 16px;
      border: 2px solid rgba(255,255,255,.3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Footer */
    .login-footer {
      text-align: center;
      font-size: 11px;
      color: #d1caca;
      margin-top: 28px;
    }

    /* Shake animation */
    @keyframes shake {
      0%,100%{ transform: translateX(0); }
      20%    { transform: translateX(-8px); }
      40%    { transform: translateX(8px); }
      60%    { transform: translateX(-6px); }
      80%    { transform: translateX(6px); }
    }
    .shake { animation: shake .4s; }

    /* Stagger fade-in */
    @keyframes fade-up {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fade-up .55s cubic-bezier(.22,1,.36,1) both; }
    .fade-up-1 { animation-delay: .05s; }
    .fade-up-2 { animation-delay: .12s; }
    .fade-up-3 { animation-delay: .19s; }
    .fade-up-4 { animation-delay: .26s; }
    .fade-up-5 { animation-delay: .33s; }

    /* Responsive */
    @media (max-width: 768px) {
      .panel-left { display: none; }
      .panel-right { width: 100%; padding: 40px 28px; }
    }
  </style>
</head>
<body>

<!-- ══ PANEL KIRI ══ -->
<div class="panel-left">
  <!-- Garis dekoratif -->
  <div class="deco-lines">
    <span style="left:15%;height:100%;transform:rotate(12deg);top:-10%;"></span>
    <span style="left:40%;height:100%;transform:rotate(8deg);top:-5%;"></span>
    <span style="left:65%;height:100%;transform:rotate(15deg);top:-8%;"></span>
    <span style="left:85%;height:100%;transform:rotate(6deg);top:-3%;"></span>
  </div>

  <!-- Ilustrasi: Logo Paskibraka Resmi -->
  <div class="paskibraka-scene">
    <!-- Aura rings -->
    <div class="aura-ring"></div>
    <div class="aura-ring"></div>
    <div class="aura-ring"></div>

    <!-- Partikel bintang -->
    <div class="star-particle" style="left:40px;top:60px;animation-delay:0s;"></div>
    <div class="star-particle" style="left:260px;top:80px;animation-delay:.8s;width:3px;height:3px;"></div>
    <div class="star-particle" style="left:60px;top:220px;animation-delay:1.4s;width:2px;height:2px;"></div>
    <div class="star-particle" style="left:255px;top:200px;animation-delay:.4s;"></div>
    <div class="star-particle" style="left:30px;top:150px;animation-delay:2s;width:3px;height:3px;"></div>
    <div class="star-particle" style="left:280px;top:155px;animation-delay:1.2s;width:2px;height:2px;"></div>
    <div class="star-particle" style="left:150px;top:20px;animation-delay:.6s;width:4px;height:4px;"></div>

    <!-- Logo resmi Paskibraka floating -->
    <div class="logo-float-wrap">
      <img src="<?= BASE_URL ?>/assets/img/logo-paskibraka.png"
           alt="Logo Paskibraka"
           class="logo-float">
      <!-- Cahaya di bawah logo -->
      <div class="logo-glow"></div>
    </div>
  </div>

  <!-- Teks kiri -->
  <h2 class="panel-left-title">Seleksi<br><em>Paskibraka</em><br>Terbaik</h2>
  <p class="panel-left-sub">Sistem klasifikasi kelayakan peserta menggunakan algoritma K-Nearest Neighbor yang akurat dan terukur.</p>

  <!-- Mini flags -->
  <div class="mini-flags">
    <div class="mini-flag"><div class="mf-red"></div><div class="mf-white"></div></div>
    <div class="mini-flag"><div class="mf-red"></div><div class="mf-white"></div></div>
    <div class="mini-flag"><div class="mf-red"></div><div class="mf-white"></div></div>
  </div>

  <div class="year-badge">
    <i class="fas fa-calendar-alt" style="font-size:10px;"></i>
    Seleksi <?= getSetting('tahun_seleksi', date('Y')) ?>
  </div>
</div>

<!-- ══ PANEL KANAN ══ -->
<div class="panel-right">
  <div class="login-inner">

    <!-- Logo app -->
    <div class="login-logo-wrap fade-up fade-up-1">
      <div class="login-flag">
        <div class="f-red"></div>
        <div class="f-white"></div>
      </div>
      <div>
        <div class="login-app-name"><?= APP_NAME ?></div>
        <div class="login-app-sub">Panel Administrasi</div>
      </div>
    </div>

    <!-- Heading -->
    <div class="fade-up fade-up-2">
      <h1 class="login-heading">Selamat Datang</h1>
      <p class="login-heading-sub">Masuk untuk mengelola data seleksi</p>
      <div class="login-divider"></div>
    </div>

    <!-- Error -->
    <?php if ($error): ?>
    <div class="err-alert fade-up fade-up-2" id="errAlert">
      <i class="fas fa-circle-exclamation"></i>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" id="formLogin">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

      <div class="field-wrap fade-up fade-up-3">
        <label class="field-label">Username</label>
        <i class="fas fa-user field-icon"></i>
        <input type="text" name="username" class="field-input"
               placeholder="Masukkan username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               autocomplete="username" required autofocus>
      </div>

      <div class="field-wrap fade-up fade-up-4">
        <label class="field-label">Password</label>
        <i class="fas fa-lock field-icon"></i>
        <input type="password" name="password" id="inputPw" class="field-input"
               placeholder="Masukkan password"
               autocomplete="current-password" required>
        <button type="button" onclick="togglePw()" class="field-eye" id="btnEye">
          <i class="fas fa-eye" id="eyeIcon"></i>
        </button>
      </div>

      <button type="submit" class="btn-masuk fade-up fade-up-5" id="btnMasuk">
        <div class="spinner" id="spinner"></div>
        <i class="fas fa-arrow-right-to-bracket" id="btnIcon"></i>
        <span id="btnText">Masuk</span>
      </button>
    </form>

    <p class="login-footer">
      &copy; <?= date('Y') ?> <?= APP_NAME ?> &nbsp;&middot;&nbsp; <?= APP_TAGLINE ?>
    </p>
  </div>
</div>

<script>
function togglePw() {
  const i = document.getElementById('inputPw');
  const e = document.getElementById('eyeIcon');
  if (i.type === 'password') { i.type = 'text'; e.className = 'fas fa-eye-slash'; }
  else { i.type = 'password'; e.className = 'fas fa-eye'; }
}

<?php if ($error): ?>
document.getElementById('formLogin')?.classList.add('shake');
<?php endif; ?>

document.getElementById('formLogin')?.addEventListener('submit', function() {
  const btn    = document.getElementById('btnMasuk');
  const spin   = document.getElementById('spinner');
  const icon   = document.getElementById('btnIcon');
  const txt    = document.getElementById('btnText');
  spin.style.display = 'block';
  icon.style.display = 'none';
  txt.textContent    = 'Memproses...';
  btn.disabled       = true;
  btn.style.opacity  = '.8';
});
</script>
</body>
</html>
