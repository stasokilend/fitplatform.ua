<?php
// api/admin/delete-exercise.php - Видалення вправи

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../classes/Database.php';

// Перевірка прав
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

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'Відсутній ID вправи']);
    exit;
}

$exerciseId = $data['id'];
$db->query("DELETE FROM exercises WHERE id = ?", [$exerciseId]);

echo json_encode(['success' => true, 'message' => 'Вправу видалено']);
?>