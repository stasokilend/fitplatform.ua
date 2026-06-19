<?php
// api/admin/check.php - Перевірка прав адміністратора

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../classes/Database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизований']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

$user = $db->fetchOne("SELECT role FROM users WHERE id = ?", [$userId]);

if (!$user || !in_array($user['role'], ['admin', 'trainer'])) {
    echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
    exit;
}

echo json_encode(['success' => true, 'role' => $user['role']]);
?>