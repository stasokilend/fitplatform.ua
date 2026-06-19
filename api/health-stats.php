<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/HealthController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$health = new HealthController($userId);

$stats = $health->getHealthStats();
$history = $health->getHeartRateHistory(30);

echo json_encode([
    'success' => true,
    'data' => [
        'stats' => $stats,
        'history' => $history,
        'zones' => $stats['target_zones']
    ]
]);
?>