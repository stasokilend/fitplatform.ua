<?php
// api/update-hr.php - Оновлення даних пульсу під час тренування

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['session_id']) || !isset($data['bpm'])) {
    echo json_encode(['success' => false, 'error' => 'Відсутні дані']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$sessionId = $data['session_id'];
$bpm = (int)$data['bpm'];

// Збереження пульсу
$db->insert('heart_rate_logs', [
    'session_id' => $sessionId,
    'timestamp' => date('Y-m-d H:i:s'),
    'bpm' => $bpm
]);

// Отримання профілю користувача
$user = new User($userId);
$profile = $user->getData();

// Розрахунок цільової зони пульсу
$restHR = 70; // За замовчуванням, можна додати поле в профіль
$zones = $user->getTargetHRZones($profile['age'] ?? 25, $restHR);

// Визначення поточної зони
$zone = 'normal';
$recommendation = 'Продовжуйте';
if ($bpm < $zones['warmup']['low']) {
    $zone = 'low';
    $recommendation = 'Збільште інтенсивність';
} elseif ($bpm >= $zones['anaerobic']['low']) {
    $zone = 'danger';
    $recommendation = 'Зменште темп, відпочиньте';
} elseif ($bpm >= $zones['aerobic']['low']) {
    $zone = 'high';
    $recommendation = 'Відмінно, тримайте темп';
}

echo json_encode([
    'success' => true,
    'bpm' => $bpm,
    'zone' => $zone,
    'recommendation' => $recommendation,
    'zones' => [
        'low' => round($zones['warmup']['low']),
        'high' => round($zones['anaerobic']['low'])
    ]
]);
?>