<?php
// api/get-achievements.php - Отримання досягнень користувача

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../classes/Database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизований']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Всі досягнення з позначкою, чи отримані
$achievements = $db->fetchAll("
    SELECT a.*, 
           CASE WHEN ua.id IS NOT NULL THEN 1 ELSE 0 END as unlocked,
           ua.unlocked_at
    FROM achievements a
    LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
    ORDER BY a.id
", [$userId]);

echo json_encode(['success' => true, 'achievements' => $achievements]);
?>