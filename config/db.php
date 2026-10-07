<?php
/**
 * config/db.php — Konfigurasi Database & Konstanta Aplikasi
 */

// ── Konfigurasi Database ──────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'db_sipaskibraka');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ── Konstanta Aplikasi ────────────────────────────────────
define('APP_NAME',    'SiPaskibraka');
define('APP_TAGLINE', 'Sistem Klasifikasi Kelayakan Seleksi Paskibraka');
define('BASE_URL',    'http://localhost/sipaskibraka');
define('KNN_K',       3);

// ── PDO Singleton ─────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET,
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die('<div style="font:14px monospace;background:#fef2f2;color:#7f1d1d;padding:24px;border-radius:10px;margin:30px;">
                <strong>❌ Koneksi Database Gagal</strong><br><br>
                Periksa konfigurasi di <code>config/db.php</code><br>
                <small>'.htmlspecialchars($e->getMessage()).'</small>
            </div>');
        }
    }
    return $pdo;
}

// ── Pengaturan ────────────────────────────────────────────
function getSetting(string $key, string $default = ''): string {
    try {
        $s = getDB()->prepare("SELECT nilai FROM pengaturan WHERE kunci=? LIMIT 1");
        $s->execute([$key]);
        $r = $s->fetch();
        return $r ? $r['nilai'] : $default;
    } catch (Throwable $e) { return $default; }
}

function setSetting(string $key, string $value): void {
    try {
        getDB()->prepare("INSERT INTO pengaturan (kunci,nilai) VALUES (?,?) ON DUPLICATE KEY UPDATE nilai=VALUES(nilai)")
               ->execute([$key, $value]);
    } catch (Throwable $e) {}
}

// ── Log Aktivitas ─────────────────────────────────────────
function catatLog(string $aksi, string $detail = ''): void {
    try {
        $adminId = $_SESSION['admin_id'] ?? 0;
        $nama    = $_SESSION['nama']     ?? 'System';
        $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua      = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300);
        getDB()->prepare("INSERT INTO log_aktivitas (id_admin,nama_admin,aksi,detail,ip_address,user_agent) VALUES (?,?,?,?,?,?)")
               ->execute([$adminId, $nama, strtoupper($aksi), $detail, $ip, $ua]);
    } catch (Throwable $e) {}
}

// ── Helper Umum ───────────────────────────────────────────
function sanitize(string $s): string {
    return htmlspecialchars(strip_tags(trim($s)), ENT_QUOTES, 'UTF-8');
}

function generateCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRF(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flash(string $msg, string $type = 'success'): void {
    $_SESSION['flash_msg']  = $msg;
    $_SESSION['flash_type'] = $type;
}

function getFlash(): array {
    $msg  = $_SESSION['flash_msg']  ?? '';
    $type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
    return compact('msg', 'type');
}

function formatTanggal(?string $date): string {
    if (!$date) return '—';
    $bln = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    $t   = strtotime($date);
    return date('d', $t) . ' ' . $bln[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function jkLabel(string $jk): string {
    if ($jk === 'L') return '<span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700">♂ L</span>';
    if ($jk === 'P') return '<span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-pink-100 text-pink-700">♀ P</span>';
    return '—';
}

function statusBadge(string $status): string {
    return match($status) {
        'Lulus'       => '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-full bg-green-100 text-green-700"><i class="fas fa-check"></i> Lulus</span>',
        'Tidak Lulus' => '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-full bg-red-100 text-red-700"><i class="fas fa-times"></i> Tidak Lulus</span>',
        default       => '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-full bg-gray-100 text-gray-500"><i class="fas fa-clock"></i> Belum Diproses</span>',
    };
}
