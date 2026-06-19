<?php
// Запуск сессии всегда в начале
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    // Проверяем, что сессия существует и содержит user_id
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Користувач';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Будь ласка, увійдіть в систему';
        header('Location: /login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
        header('Location: /dashboard.php');
        exit;
    }
}

// Функция для отладки сессии
function debugSession() {
    echo '<pre>';
    echo 'SESSION STATUS: ' . session_status() . "\n";
    echo 'SESSION ID: ' . session_id() . "\n";
    echo 'SESSION DATA: ';
    print_r($_SESSION);
    echo '</pre>';
}
?>