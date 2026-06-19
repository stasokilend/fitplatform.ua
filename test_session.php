<?php
require_once 'includes/session.php';
require_once 'config/database.php';

echo "<h1>Тест сессии</h1>";

// Проверка сессии
debugSession();

if (isLoggedIn()) {
    echo "<p style='color:green'>✅ Пользователь авторизован: " . htmlspecialchars($_SESSION['user_name']) . "</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Role: " . $_SESSION['user_role'] . "</p>";
    
    // Проверка профиля
    require_once 'includes/auth.php';
    $completed = isProfileCompleted($_SESSION['user_id']);
    echo "<p>Profile completed: " . ($completed ? '✅ Да' : '❌ Нет') . "</p>";
    
    if (!$completed) {
        echo "<p><a href='/profile-setup.php'>Заполнить профиль</a></p>";
    } else {
        echo "<p><a href='/dashboard.php'>Перейти в кабинет</a></p>";
    }
} else {
    echo "<p style='color:red'>❌ Пользователь не авторизован</p>";
    echo "<p><a href='/login.php'>Войти</a></p>";
}

// Проверка cookies
echo "<h2>Cookies:</h2>";
print_r($_COOKIE);

echo "<h2>Session save path:</h2>";
echo session_save_path();
?>