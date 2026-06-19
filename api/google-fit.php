<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/GoogleFitController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$googleFit = new GoogleFitController($userId);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- ПОЛУЧЕНИЕ URL ДЛЯ АВТОРИЗАЦИИ ---
if ($action === 'auth_url') {
    $url = $googleFit->getAuthUrl();
    echo json_encode(['success' => true, 'url' => $url]);
    exit;
}

// --- ПРОВЕРКА ПОДКЛЮЧЕНИЯ ---
if ($action === 'status') {
    $isConnected = $googleFit->isConnected();
    $syncStatus = $googleFit->getSyncStatus();
    $userInfo = $isConnected ? $googleFit->getUserInfo() : null;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'connected' => $isConnected,
            'user' => $userInfo,
            'sync_status' => $syncStatus
        ]
    ]);
    exit;
}

// --- СИНХРОНИЗАЦИЯ ДАННЫХ ---
if ($action === 'sync') {
    $days = (int)($_POST['days'] ?? 7);
    $result = $googleFit->syncData($days);
    echo json_encode($result);
    exit;
}

// --- ОТКЛЮЧЕНИЕ ---
if ($action === 'disconnect') {
    $result = $googleFit->disconnect();
    echo json_encode(['success' => $result]);
    exit;
}

// --- ПОЛУЧЕНИЕ СИНХРОНИЗИРОВАННЫХ ДАННЫХ ---
if ($action === 'data') {
    $days = (int)($_GET['days'] ?? 7);
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime("-$days days"));
    
    $stmt = $pdo->prepare("
        SELECT * FROM user_activity_data 
        WHERE user_id = ? AND activity_date BETWEEN ? AND ?
        ORDER BY activity_date ASC
    ");
    $stmt->execute([$userId, $startDate, $endDate]);
    $data = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Невідома дія']);
?>