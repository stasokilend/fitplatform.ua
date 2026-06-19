<?php
require_once 'includes/session.php';
require_once 'controllers/GoogleFitController.php';

session_start();

if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$code = $_GET['code'] ?? '';

if (!$code) {
    $_SESSION['error'] = 'Не вдалося авторизуватися через Google Fit';
    header('Location: /dashboard.php?page=google-fit');
    exit;
}

$googleFit = new GoogleFitController($userId);
$success = $googleFit->exchangeCode($code);

if ($success) {
    $_SESSION['success'] = 'Google Fit успішно підключено!';
} else {
    $_SESSION['error'] = 'Помилка підключення Google Fit';
}

header('Location: /dashboard.php?page=google-fit');
exit;
?>