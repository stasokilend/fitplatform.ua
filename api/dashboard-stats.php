<?php
// api/dashboard-stats.php - Статистика для дашборда користувача

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Загальна статистика
$stats = [];

// Всього тренувань
$stats['total_sessions'] = $db->fetchOne(
    "SELECT COUNT(*) as count FROM training_sessions WHERE user_id = ?",
    [$userId]
)['count'] ?? 0;

// Тренувань за останні 7 днів
$stats['sessions_week'] = $db->fetchOne(
    "SELECT COUNT(*) as count FROM training_sessions 
     WHERE user_id = ? AND started_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
    [$userId]
)['count'] ?? 0;

// Всього хвилин
$stats['total_minutes'] = $db->fetchOne(
    "SELECT SUM(duration_minutes) as sum FROM training_sessions WHERE user_id = ?",
    [$userId]
)['sum'] ?? 0;

// Всього калорій
$stats['total_calories'] = $db->fetchOne(
    "SELECT SUM(calories_burned) as sum FROM training_sessions WHERE user_id = ?",
    [$userId]
)['sum'] ?? 0;

// Останнє тренування
$stats['last_session'] = $db->fetchOne(
    "SELECT started_at, duration_minutes, calories_burned 
     FROM training_sessions 
     WHERE user_id = ? 
     ORDER BY started_at DESC LIMIT 1",
    [$userId]
);

// Прогрес по вправах (топ-3 найчастіші)
$stats['top_exercises'] = $db->fetchAll(
    "SELECT e.name, COUNT(*) as count 
     FROM training_sessions s
     JOIN plan_exercises pe ON s.plan_id = pe.plan_id
     JOIN exercises e ON pe.exercise_id = e.id
     WHERE s.user_id = ?
     GROUP BY e.id
     ORDER BY count DESC
     LIMIT 3",
    [$userId]
);

// Поточна активна сесія
$stats['active_session'] = $db->fetchOne(
    "SELECT id FROM training_sessions 
     WHERE user_id = ? AND ended_at IS NULL 
     ORDER BY started_at DESC LIMIT 1",
    [$userId]
);

echo json_encode(['success' => true, 'stats' => $stats]);
?>