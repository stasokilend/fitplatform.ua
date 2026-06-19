<?php
// api/admin/delete-user.php - Видалення користувача

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
    echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Відсутній ID користувача']);
    exit;
}

$targetUserId = $data['user_id'];

// Не дозволяємо видаляти себе
if ($targetUserId == $adminId) {
    echo json_encode(['success' => false, 'error' => 'Не можна видалити власний акаунт']);
    exit;
}

// Перевірка, чи існує користувач
$user = $db->fetchOne("SELECT id FROM users WHERE id = ?", [$targetUserId]);
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Користувач не знайдений']);
    exit;
}

// Видалення (каскадно видаляться всі пов'язані дані)
$db->query("DELETE FROM users WHERE id = ?", [$targetUserId]);

echo json_encode(['success' => true, 'message' => 'Користувача видалено']);
?>