<?php
require_once 'config/database.php';
require_once 'controllers/GamificationController.php';

echo "=== Синхронизация достижений для всех пользователей ===\n\n";

// Получаем всех пользователей
$stmt = $pdo->query("SELECT id, full_name FROM users WHERE role != 'admin' ORDER BY id");
$users = $stmt->fetchAll();

$totalUsers = count($users);
$processed = 0;
$unlockedTotal = 0;

foreach ($users as $user) {
    $processed++;
    echo "[$processed/$totalUsers] Пользователь: {$user['full_name']} (ID: {$user['id']})... ";
    
    $gamification = new GamificationController($user['id']);
    $stats = $gamification->syncAchievements();
    
    $unlocked = $stats['completed_achievements'] ?? 0;
    $unlockedTotal += $unlocked;
    
    echo "Достижений: $unlocked\n";
}

echo "\n=== Готово! ===\n";
echo "Всего пользователей: $totalUsers\n";
echo "Всего разблокировано достижений: $unlockedTotal\n";
echo "Запустите этот скрипт через браузер или консоль: php sync_achievements.php\n";