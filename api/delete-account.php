<?php
// api/delete-account.php - Видалення власного аккаунта

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Видалення користувача (каскадно видаляться всі пов'язані дані)
$db->query("DELETE FROM users WHERE id = ?", [$userId]);

// Завершення сесії
session_destroy();

echo json_encode(['success' => true, 'message' => 'Аккаунт видалено']);
?>