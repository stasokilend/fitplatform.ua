<?php
// api/update-user.php - Оновлення даних користувача (ім'я, email)

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Немає даних']);
    exit;
}

$updates = [];
$params = ['user_id' => $userId];

if (isset($data['full_name']) && trim($data['full_name']) !== '') {
    $updates[] = 'full_name = :full_name';
    $params['full_name'] = trim($data['full_name']);
}

if (isset($data['email']) && trim($data['email']) !== '') {
    // Перевірка, чи email не зайнятий
    $check = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$data['email'], $userId]);
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

$sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :user_id";
$db->query($sql, $params);

echo json_encode(['success' => true, 'message' => 'Дані оновлено']);
?>