<?php
// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/Csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Недійсний CSRF-токен. Оновіть сторінку і спробуйте ще раз.';
        header('Location: ' . url('/login.php'));
        exit;
    }
    $action = $_POST['action'] ?? '';
    
    if ($action === 'register') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        
        $result = registerUser($email, $password, $fullName);
        
        if ($result['success']) {
            // Автоматический вход после регистрации
            $loginResult = loginUser($email, $password);
            if ($loginResult['success']) {
                header('Location: ' . url('/profile-setup.php'));
                exit;
            }
        } else {
            $_SESSION['error'] = $result['error'];
            header('Location: ' . url('/register.php'));
            exit;
        }
    }
    
    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            // Проверяем, что сессия действительно создалась
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Помилка сесії. Спробуйте ще раз.';
                header('Location: ' . url('/login.php'));
                exit;
            }
            
            // Проверка заполнения профиля
            require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/Csrf.php';
            $profileCompleted = isProfileCompleted($_SESSION['user_id']);
            
            if (!$profileCompleted) {
                header('Location: ' . url('/profile-setup.php'));
                exit;
            } else {
                header('Location: ' . url('/dashboard.php'));
                exit;
            }
        } else {
            $_SESSION['error'] = $result['error'];
            header('Location: ' . url('/login.php'));
            exit;
        }
    }
    
    if ($action === 'logout') {
        logoutUser();
    }
}

// Если не POST - перенаправляем на главную
header('Location: ' . url('/index.php'));
exit;
?>