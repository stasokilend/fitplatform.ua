<?php
// api/login.php - Вхід користувача

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/User.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'error' => 'Введіть email та пароль']);
    exit;
}

$user = new User();
$result = $user->login($data['email'], $data['password']);

if ($result) {
    // Запуск сесії для збереження стану
    session_start();
    $_SESSION['user_id'] = $user->getId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Вхід виконано',
        'user' => $user->getData()
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Невірний email або пароль']);
}
?>