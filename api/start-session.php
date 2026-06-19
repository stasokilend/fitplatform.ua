<?php
// api/start-session.php - Початок тренування

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/session-helpers.php';

$userId = requireJsonAuth();
$data = getJsonInput();
$planId = $data['plan_id'] ?? null;

$db = Database::getInstance();

if ($planId !== null) {
    $planId = filter_var($planId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$planId) {
        echo json_encode(['success' => false, 'error' => 'Некоректний ID плану']);
        exit;
    }

    $plan = $db->fetchOne('SELECT id FROM workout_plans WHERE id = ? AND user_id = ?', [$planId, $userId]);
    if (!$plan) {
        echo json_encode(['success' => false, 'error' => 'План не знайдено']);
        exit;
    }
}

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