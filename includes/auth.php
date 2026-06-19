<?php
// includes/auth.php - Допоміжні функції для авторизації

function isAuthenticated() {
    session_start();
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: /login.html');
        exit;
    }
}

function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    require_once __DIR__ . '/../classes/User.php';
    return new User($_SESSION['user_id']);
}

function logout() {
    session_start();
    session_destroy();
    header('Location: /login.html');
    exit;
}
?>