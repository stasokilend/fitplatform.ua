<?php
// api/get-recent-sessions.php - Отримання тренувань за останні N днів

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../classes/Database.php';

$days = $_GET['days'] ?? 7;

$db = Database::getInstance();
$sessions = $db->fetchAll("
    SELECT DATE(started_at) as date, COUNT(*) as count 
    FROM training_sessions 
    WHERE started_at > DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY DATE(started_at)
", [$days]);

echo json_encode(['success' => true, 'sessions' => $sessions]);
?>