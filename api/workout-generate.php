<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/WorkoutGenerator.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'generate';

// --- ГЕНЕРАЦИЯ ТРЕНИРОВКИ ---
if ($action === 'generate') {
    $duration = (int)($_POST['duration'] ?? 30);
    $focus = $_POST['focus'] ?? null;
    $name = trim($_POST['name'] ?? 'Моє тренування');
    
    // Генерируем тренировку
    $result = createWorkoutFromGeneration($userId, $name, $duration, $focus);
    
    echo json_encode($result);
    exit;
}

// --- СОХРАНЕНИЕ ТРЕНИРОВКИ ---
if ($action === 'save') {
    $planId = (int)($_POST['plan_id'] ?? 0);
    $name = trim($_POST['name'] ?? 'Моє тренування');
    
    if (!$planId) {
        echo json_encode(['success' => false, 'error' => 'ID тренування не вказано']);
        exit;
    }
    
    // Проверяем, что план принадлежит пользователю
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM workout_plans WHERE id = ? AND user_id = ?");
    $stmt->execute([$planId, $userId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
        exit;
    }
    
    // Обновляем название
    $stmt = $pdo->prepare("UPDATE workout_plans SET name = ? WHERE id = ?");
    $success = $stmt->execute([$name, $planId]);
    
    echo json_encode(['success' => $success]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Невідома дія']);
?>