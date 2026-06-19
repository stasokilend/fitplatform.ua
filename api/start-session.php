<?php
// api/start-session.php - Початок тренування

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$planId = $data['plan_id'] ?? null;

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Створення сесії
$sessionId = $db->insert('training_sessions', [
    'user_id' => $userId,
    'plan_id' => $planId,
    'started_at' => date('Y-m-d H:i:s')
]);

echo json_encode([
    'success' => true,
    'session_id' => $sessionId,
    'message' => 'Тренування розпочато'
]);
?>