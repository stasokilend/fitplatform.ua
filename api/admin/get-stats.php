<?php
// api/admin/get-stats.php - Загальна статистика системи

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../classes/Database.php';

// Перевірка прав
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизований']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$user = $db->fetchOne("SELECT role FROM users WHERE id = ?", [$userId]);

if (!$user || !in_array($user['role'], ['admin', 'trainer'])) {
    echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
    exit;
}

// Загальна статистика
$stats = [];

// Кількість користувачів
$stats['total_users'] = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];

// Кількість тренерів
$stats['total_trainers'] = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'trainer'")['count'];

// Кількість адмінів
$stats['total_admins'] = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count'];

// Кількість вправ
$stats['total_exercises'] = $db->fetchOne("SELECT COUNT(*) as count FROM exercises")['count'];

// Кількість тренувань
$stats['total_sessions'] = $db->fetchOne("SELECT COUNT(*) as count FROM training_sessions")['count'];

// Кількість тренувань за останні 7 днів
$stats['sessions_last_7_days'] = $db->fetchOne(
    "SELECT COUNT(*) as count FROM training_sessions WHERE started_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"
)['count'];

// Загальна кількість хвилин тренувань
$stats['total_minutes'] = $db->fetchOne("SELECT SUM(duration_minutes) as sum FROM training_sessions")['sum'] ?? 0;

// Останні 5 користувачів
$stats['recent_users'] = $db->fetchAll(
    "SELECT id, full_name, email, created_at FROM users ORDER BY id DESC LIMIT 5"
);

// Останні 5 тренувань
$stats['recent_sessions'] = $db->fetchAll(
    "SELECT s.*, u.full_name FROM training_sessions s 
     JOIN users u ON s.user_id = u.id 
     ORDER BY s.id DESC LIMIT 5"
);

echo json_encode(['success' => true, 'stats' => $stats]);
?>