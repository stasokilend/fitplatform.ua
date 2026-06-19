<?php
// api/change-password.php - Зміна пароля користувача

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

if (!isset($data['current_password']) || !isset($data['new_password']) || !isset($data['confirm_password'])) {
    echo json_encode(['success' => false, 'error' => 'Заповніть всі поля']);
    exit;
}

// Перевірка поточного пароля
$user = $db->fetchOne("SELECT password_hash FROM users WHERE id = ?", [$userId]);
if (!$user || !password_verify($data['current_password'], $user['password_hash'])) {
    echo json_encode(['success' => false, 'error' => 'Поточний пароль невірний']);
    exit;
}

if (strlen($data['new_password']) < 6) {
    echo json_encode(['success' => false, 'error' => 'Новий пароль має бути не менше 6 символів']);
    exit;
}

if ($data['new_password'] !== $data['confirm_password']) {
    echo json_encode(['success' => false, 'error' => 'Паролі не співпадають']);
    exit;
}

// Зміна пароля
$newHash = password_hash($data['new_password'], PASSWORD_DEFAULT);
$db->query("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $userId]);

echo json_encode(['success' => true, 'message' => 'Пароль змінено']);
?>