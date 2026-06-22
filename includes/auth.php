<?php
require_once __DIR__ . '/../config/database.php';

function registerUser($email, $password, $fullName) {
    global $pdo;
    
    // Проверка существования
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Користувач з таким email вже існує'];
    }
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES (?, ?, ?, 'user')");
    
    if ($stmt->execute([$email, $hash, $fullName])) {
        $userId = $pdo->lastInsertId();
        
        // Создаем пустой профиль
        $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id, profile_completed) VALUES (?, 0)");
        $stmt->execute([$userId]);
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    return ['success' => false, 'error' => 'Помилка реєстрації'];
}

function loginUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Очищаем старую сессию и защищаем от session fixation
        $_SESSION = array();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        
        // Устанавливаем новые значения
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        
        // Проверяем, что сессия сохранилась
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            return ['success' => false, 'error' => 'Помилка створення сесії'];
        }
        
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'Невірний email або пароль'];
}

function logoutUser() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: ' . url('/index.php'));
    exit;
}

function isProfileCompleted($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT profile_completed FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    if (!$result) {
        return false;
    }
    
    return (bool)$result['profile_completed'];
}

// Проверка, является ли пользователь тренером
function isTrainer() {
    return function_exists('getEffectiveUserRole') ? getEffectiveUserRole() === 'trainer' : (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'trainer');
}

/**
 * Проверка, является ли пользователь администратором
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Проверка, является ли пользователь обычным пользователем
 */
function isUser() {
    return function_exists('getEffectiveUserRole') ? getEffectiveUserRole() === 'user' : (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user');
}
?>