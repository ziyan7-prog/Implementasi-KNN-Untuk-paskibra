<?php
/**
 * config/auth.php — Autentikasi dengan dukungan Super Admin
 */

function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireSuperAdmin(): void {
    requireAdmin();
    if (($_SESSION['role'] ?? '') !== 'superadmin') {
        http_response_code(403);
        die('<div style="font:14px system-ui;text-align:center;padding:60px;color:#7f1d1d;">
            <div style="font-size:48px;margin-bottom:16px;">🔒</div>
            <h2>Akses Ditolak</h2>
            <p>Halaman ini hanya dapat diakses oleh Super Admin.</p>
            <a href="' . BASE_URL . '/admin/dashboard.php" style="color:#9f1239;">← Kembali ke Dashboard</a>
        </div>');
    }
}

function requireNotSuperAdmin(): void {
    requireAdmin();
    if (($_SESSION['role'] ?? '') === 'superadmin') {
        header('Location: ' . BASE_URL . '/admin/super_admin.php');
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function isSuperAdmin(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'superadmin';
}

function loginAdmin(string $username, string $password): array {
    try {
        $s = getDB()->prepare("SELECT * FROM admins WHERE username=? AND is_active=1 LIMIT 1");
        $s->execute([$username]);
        $admin = $s->fetch();
        if (!$admin || !password_verify($password, $admin['password'])) {
            return ['ok' => false, 'msg' => 'Username atau password salah.'];
        }
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['nama']     = $admin['nama'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['role']     = $admin['role'] ?? 'admin';
        getDB()->prepare("UPDATE admins SET last_login=NOW() WHERE id=?")->execute([$admin['id']]);
        catatLog('LOGIN', 'Admin login berhasil');
        return ['ok' => true];
    } catch (Throwable $e) {
        return ['ok' => false, 'msg' => 'Terjadi kesalahan sistem.'];
    }
}

function logoutAdmin(): void {
    catatLog('LOGOUT', 'Admin logout');
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
