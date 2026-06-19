<?php
// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверка авторизации админа
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

// Требование авторизации
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Требование супер-админа
function requireSuperAdmin() {
    requireAdminLogin();
    if ($_SESSION['admin_role'] !== 'admin') {
        die('Доступ заборонено. Потрібні права супер-адміністратора.');
    }
}

// Вход админа
function adminLogin($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' AND is_active = 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_email'] = $admin['email'];
        return true;
    }
    
    return false;
}

// Выход админа
function adminLogout() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_role']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_email']);
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}

// Получение статистики для админ-панели
function getAdminStats() {
    global $pdo;
    
    $stats = [];
    
    // Всего пользователей
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $stmt->fetch()['total'] ?? 0;
    
    // Активных пользователей (с профилем)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM user_profiles WHERE profile_completed = 1");
    $stats['active_users'] = $stmt->fetch()['total'] ?? 0;
    
    // Тренеров
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'trainer'");
    $stats['total_trainers'] = $stmt->fetch()['total'] ?? 0;
    
    // Всего упражнений
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM exercises WHERE is_active = 1");
    $stats['total_exercises'] = $stmt->fetch()['total'] ?? 0;
    
    // Всего тренировок
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM workout_plans");
    $stats['total_workouts'] = $stmt->fetch()['total'] ?? 0;
    
    // Завершенных тренировок
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM workout_plans WHERE status = 'completed'");
    $stats['completed_workouts'] = $stmt->fetch()['total'] ?? 0;
    
    // Новые пользователи за сегодня
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()");
    $stats['new_today'] = $stmt->fetch()['total'] ?? 0;
    
    return $stats;
}
?>