<?php
require_once __DIR__ . '/../config/Env.php';
require_once __DIR__ . '/functions.php';
Env::load();

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (string) (Env::int('SESSION_LIFETIME', 120) * 60));
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUserRole() { return $_SESSION['user_role'] ?? null; }
function getUserId() { return $_SESSION['user_id'] ?? null; }
function getUserName() { return $_SESSION['user_name'] ?? 'Користувач'; }

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Будь ласка, увійдіть в систему';
        header('Location: ' . url('/login.php'));
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
        header('Location: ' . url('/dashboard.php'));
        exit;
    }
}

function debugSession() {
    echo '<pre>';
    echo 'SESSION STATUS: ' . session_status() . "\n";
    echo 'SESSION ID: ' . session_id() . "\n";
    echo 'SESSION DATA: ';
    print_r($_SESSION);
    echo '</pre>';
}
?>
