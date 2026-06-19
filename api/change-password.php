<?php
// api/change-password.php - Зміна пароля користувача

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

if (!isset($data['current_password']) || !isset($data['new_password'])) {
    echo json_encode(['success' => false, 'error' => 'Введіть поточний та новий пароль']);
    exit;
}

// Перевірка поточного пароля
$user = $db->fetchOne("SELECT password_hash FROM users WHERE id = ?", [$userId]);
if (!$user || !password_verify($data['current_password'], $user['password_hash'])) {
    echo json_encode(['success' => false, 'error' => 'Невірний поточний пароль']);
    exit;
}

if (strlen($data['new_password']) < 6) {
    echo json_encode(['success' => false, 'error' => 'Новий пароль має бути не менше 6 символів']);
    exit;
}

$newHash = password_hash($data['new_password'], PASSWORD_DEFAULT);
$db->query("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $userId]);

echo json_encode(['success' => true, 'message' => 'Пароль змінено']);
?>