<?php
// api/profile.php - Робота з профілем користувача

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$user = new User($_SESSION['user_id']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Отримання профілю з роллю
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    $sql = "SELECT u.*, p.* FROM users u 
            LEFT JOIN user_profiles p ON u.id = p.user_id 
            WHERE u.id = ?";
    $profile = $db->fetchOne($sql, [$userId]);
    if ($profile) {
        unset($profile['password_hash']);
        echo json_encode(['success' => true, 'profile' => $profile]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Користувача не знайдено']);
    }
} elseif ($method === 'POST' || $method === 'PUT') {
    // Збереження профілю (без змін)
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Немає даних']);
        exit;
    }

    $required = ['age', 'weight', 'height', 'gender', 'fitness_level', 'goals'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            echo json_encode(['success' => false, 'error' => "Поле '$field' обов'язкове"]);
            exit;
        }
    }

    $bmr = $user->calculateBMR($data['weight'], $data['height'], $data['age'], $data['gender']);
    $data['bmr'] = $bmr;

    $result = $user->saveProfile($data);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Профіль збережено', 'bmr' => $bmr]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Помилка збереження']);
    }
}