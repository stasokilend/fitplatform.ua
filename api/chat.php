<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/ChatController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$chat = new ChatController($userId);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- ПОЛУЧЕНИЕ СПИСКА ЧАТОВ ---
if ($action === 'list') {
    $chats = $chat->getChats();
    echo json_encode(['success' => true, 'data' => $chats]);
    exit;
}

// --- ПОЛУЧЕНИЕ СООБЩЕНИЙ ---
if ($action === 'messages') {
    $chatId = (int)($_GET['chat_id'] ?? 0);
    if (!$chatId) {
        echo json_encode(['success' => false, 'error' => 'Chat ID не вказано']);
        exit;
    }
    
    $messages = $chat->getMessages($chatId);
    $chat->markAsRead($chatId);
    echo json_encode(['success' => true, 'data' => $messages]);
    exit;
}

// --- ОТПРАВКА СООБЩЕНИЯ ---
if ($action === 'send') {
    $chatId = (int)($_POST['chat_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    if (!$chatId || !$message) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    $success = $chat->sendMessage($chatId, $message);
    echo json_encode(['success' => $success]);
    exit;
}

// --- СОЗДАНИЕ ЧАТА ---
if ($action === 'create') {
    $otherUserId = (int)($_POST['user_id'] ?? 0);
    if (!$otherUserId) {
        echo json_encode(['success' => false, 'error' => 'Користувач не вказан']);
        exit;
    }
    
    $chatId = $chat->getOrCreateChat($otherUserId);
    echo json_encode(['success' => true, 'chat_id' => $chatId]);
    exit;
}

// --- КОЛИЧЕСТВО НЕПРОЧИТАННЫХ ---
if ($action === 'unread') {
    $count = $chat->getUnreadCount();
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Невідома дія']);
?>