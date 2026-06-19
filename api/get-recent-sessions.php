<?php
// api/get-recent-sessions.php - Отримання тренувань за останні N днів

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/session-helpers.php';

$userId = requireJsonAuth();
$days = filter_var($_GET['days'] ?? 7, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 365]]) ?: 7;

$db = Database::getInstance();
$sessions = $db->fetchAll("
    SELECT DATE(started_at) as date, COUNT(*) as count 
    FROM training_sessions 
    WHERE user_id = ? AND started_at > DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY DATE(started_at)
", [$userId, $days]);

echo json_encode(['success' => true, 'sessions' => $sessions]);
?>