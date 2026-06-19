<?php
// api/admin/get-users.php - Отримання всіх користувачів

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

// Отримання всіх користувачів з профілями
$users = $db->fetchAll("
    SELECT u.id, u.email, u.full_name, u.role, u.created_at,
           p.age, p.weight, p.height, p.gender, p.fitness_level
    FROM users u
    LEFT JOIN user_profiles p ON u.id = p.user_id
    ORDER BY u.id
");

echo json_encode(['success' => true, 'users' => $users]);
?>