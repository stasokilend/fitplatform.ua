<?php
// api/update-account.php - Оновлення даних аккаунта

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../classes/Database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизований']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);

$updates = [];
$params = ['id' => $userId];

if (isset($data['full_name']) && !empty(trim($data['full_name']))) {
    $updates[] = 'full_name = :full_name';
    $params['full_name'] = trim($data['full_name']);
}

if (isset($data['email']) && !empty(trim($data['email']))) {
    // Перевірка, чи email не зайнятий
    $check = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [trim($data['email']), $userId]);
    if ($check) {
        echo json_encode(['success' => false, 'error' => 'Цей email вже використовується']);
        exit;
    }
    $updates[] = 'email = :email';
    $params['email'] = trim($data['email']);
}

if (empty($updates)) {
    echo json_encode(['success' => false, 'error' => 'Немає даних для оновлення']);
    exit;
}

$sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
$db->query($sql, $params);

echo json_encode(['success' => true, 'message' => 'Дані оновлено']);
?>