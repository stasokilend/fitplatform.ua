<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$notification = new NotificationController($userId);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- ПОЛУЧЕНИЕ УВЕДОМЛЕНИЙ ---
if ($action === 'get' || !$action) {
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    $notifications = $notification->getApiNotifications($limit, $offset);
    $unreadCount = $notification->getUnreadCount();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'total' => count($notifications)
        ]
    ]);
    exit;
}

// --- ПОЛУЧЕНИЕ КОЛИЧЕСТВА НЕПРОЧИТАННЫХ ---
if ($action === 'count') {
    $count = $notification->getUnreadCount();
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

// --- ОТМЕТИТЬ КАК ПРОЧИТАННОЕ ---
if ($action === 'read') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID не вказано']);
        exit;
    }
    
    $success = $notification->markAsRead($id);
    echo json_encode(['success' => $success]);
    exit;
}

// --- ОТМЕТИТЬ ВСЕ КАК ПРОЧИТАННЫЕ ---
if ($action === 'read_all') {
    $success = $notification->markAllAsRead();
    echo json_encode(['success' => $success]);
    exit;
}

// --- УДАЛИТЬ УВЕДОМЛЕНИЕ ---
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID не вказано']);
        exit;
    }
    
    $success = $notification->delete($id);
    echo json_encode(['success' => $success]);
    exit;
}

// --- УДАЛИТЬ ВСЕ УВЕДОМЛЕНИЯ ---
if ($action === 'delete_all') {
    $success = $notification->deleteAll();
    echo json_encode(['success' => $success]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Невідома дія']);
?>