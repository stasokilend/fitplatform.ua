<?php
// api/admin/update-user.php - Оновлення даних користувача

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../classes/Database.php';

// Перевірка прав (тільки адмін)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизований']);
    exit;
}

$db = Database::getInstance();
$adminId = $_SESSION['user_id'];
$admin = $db->fetchOne("SELECT role FROM users WHERE id = ?", [$adminId]);

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Доступ заборонено (потрібна роль admin)']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Відсутній ID користувача']);
    exit;
}

$targetUserId = $data['user_id'];

// Не дозволяємо змінювати себе
if ($targetUserId == $adminId) {
    echo json_encode(['success' => false, 'error' => 'Не можна змінювати власну роль']);
    exit;
}

$updates = [];
$params = ['user_id' => $targetUserId];

if (isset($data['role']) && in_array($data['role'], ['user', 'trainer', 'admin'])) {
    $updates[] = 'role = :role';
    $params['role'] = $data['role'];
}

if (isset($data['full_name'])) {
    $updates[] = 'full_name = :full_name';
    $params['full_name'] = $data['full_name'];
}

if (empty($updates)) {
    echo json_encode(['success' => false, 'error' => 'Немає даних для оновлення']);
    exit;
}

$sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :user_id";
$db->query($sql, $params);

echo json_encode(['success' => true, 'message' => 'Користувача оновлено']);
?>