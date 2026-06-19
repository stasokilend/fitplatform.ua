<?php
// api/finish-session.php - Завершення тренувальної сесії

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/session-helpers.php';

$userId = requireJsonAuth();
$data = getJsonInput();

if (!isset($data['session_id'])) {
    echo json_encode(['success' => false, 'error' => 'Відсутній ID сесії']);
    exit;
}

$db = Database::getInstance();
$sessionId = requireUserSession($db, $data['session_id'], $userId);

// Розрахунок статистики
$stats = $db->fetchOne(
    "SELECT COUNT(*) as hr_count, AVG(bpm) as avg_hr, MAX(bpm) as max_hr 
     FROM heart_rate_logs 
     WHERE session_id = ?",
    [$sessionId]
);

// Оновлення сесії
$db->update('training_sessions', [
    'ended_at' => date('Y-m-d H:i:s'),
    'duration_minutes' => $data['duration_minutes'] ?? 0,
    'avg_hr' => round($stats['avg_hr'] ?? 0),
    'max_hr' => $stats['max_hr'] ?? 0,
    'calories_burned' => $data['calories_burned'] ?? 0,
    'notes' => $data['notes'] ?? ''
], 'id = :id AND user_id = :user_id', ['id' => $sessionId, 'user_id' => $userId]);

echo json_encode([
    'success' => true,
    'message' => 'Тренування завершено',
    'stats' => $stats
]);
?>