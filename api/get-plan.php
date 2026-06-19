<?php
// api/get-plan.php - Отримання деталей плану

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$planId = $_GET['plan_id'] ?? null;
if (!$planId) {
    echo json_encode(['success' => false, 'error' => 'Відсутній ID плану']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Перевірка, що план належить користувачеві
$plan = $db->fetchOne(
    "SELECT * FROM workout_plans WHERE id = ? AND user_id = ?",
    [$planId, $userId]
);

if (!$plan) {
    echo json_encode(['success' => false, 'error' => 'План не знайдено']);
    exit;
}

// Отримання вправ
$exercises = $db->fetchAll(
    "SELECT e.*, pe.sets, pe.reps, pe.rest_seconds, pe.order_index 
     FROM plan_exercises pe 
     JOIN exercises e ON pe.exercise_id = e.id 
     WHERE pe.plan_id = ? 
     ORDER BY pe.order_index",
    [$planId]
);

echo json_encode([
    'success' => true,
    'plan' => [
        'id' => $plan['id'],
        'name' => $plan['name'],
        'exercises' => $exercises
    ]
]);
?>