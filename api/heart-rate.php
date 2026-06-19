<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/HealthController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$health = new HealthController($userId);

// --- СОХРАНЕНИЕ ПУЛЬСА ---
if ($action === 'save') {
    $hrRest = (int)($_POST['hr_rest'] ?? 0);
    $hrCurrent = (int)($_POST['hr_current'] ?? 0);
    
    if (!$hrRest || !$hrCurrent) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    $success = $health->saveHeartRate($hrRest, $hrCurrent);
    echo json_encode(['success' => $success]);
    exit;
}

// --- ПОЛУЧЕНИЕ СТАТИСТИКИ ---
if ($action === 'stats') {
    $stats = $health->getHealthStats();
    echo json_encode(['success' => true, 'data' => $stats]);
    exit;
}

// --- ПОЛУЧЕНИЕ ИСТОРИИ ---
if ($action === 'history') {
    $days = (int)($_GET['days'] ?? 30);
    $history = $health->getHeartRateHistory($days);
    echo json_encode(['success' => true, 'data' => $history]);
    exit;
}

// --- ОПРЕДЕЛЕНИЕ ЗОНЫ ---
if ($action === 'zone') {
    $hrCurrent = (int)($_POST['hr_current'] ?? 0);
    if (!$hrCurrent) {
        echo json_encode(['success' => false, 'error' => 'Пульс не вказано']);
        exit;
    }
    
    $zone = $health->getCurrentZone($hrCurrent);
    echo json_encode(['success' => true, 'data' => $zone]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Невідома дія']);
?>