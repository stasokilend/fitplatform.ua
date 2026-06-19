<?php
// api/register.php - Реєстрація користувача

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/User.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email']) || !isset($data['password']) || !isset($data['full_name'])) {
    echo json_encode(['success' => false, 'error' => 'Не всі поля заповнені']);
    exit;
}

$user = new User();
$result = $user->register($data['email'], $data['password'], $data['full_name']);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Реєстрація успішна', 'user_id' => $user->getId()]);
} else {
    echo json_encode(['success' => false, 'error' => 'Email вже використовується']);
}
?>