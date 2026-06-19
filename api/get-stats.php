<?php
// api/get-stats.php - Отримання статистики користувача

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Останні 7 тренувань
$recentSessions = $db->fetchAll(
    "SELECT * FROM training_sessions 
     WHERE user_id = ? 
     ORDER BY started_at DESC 
     LIMIT 7",
    [$userId]
);

// Загальна статистика
$totalStats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_sessions,
        SUM(duration_minutes) as total_minutes,
        AVG(avg_hr) as avg_hr,
        SUM(calories_burned) as total_calories
     FROM training_sessions 
     WHERE user_id = ? AND ended_at IS NOT NULL",
    [$userId]
);

echo json_encode([
    'success' => true,
    'stats' => [
        'total' => $totalStats,
        'recent' => $recentSessions
    ]
]);
?>