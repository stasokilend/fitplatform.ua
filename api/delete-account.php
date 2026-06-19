<?php
// api/delete-account.php - Видалення аккаунта

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../classes/Database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизований']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Видалення (каскадно видаляться всі пов'язані дані через ON DELETE CASCADE)
$db->query("DELETE FROM users WHERE id = ?", [$userId]);

session_destroy();
echo json_encode(['success' => true, 'message' => 'Аккаунт видалено']);
?>