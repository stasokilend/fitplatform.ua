<?php
// api/generate-workout.php - Генерація індивідуального плану

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/WorkoutGenerator.php';

// Перевірка автентифікації
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$user = new User($_SESSION['user_id']);
$profile = $user->getData();

// Перевірка наявності профілю
if (!isset($profile['weight']) || !isset($profile['age'])) {
    echo json_encode(['success' => false, 'error' => 'Спочатку заповніть профіль користувача']);
    exit;
}

$generator = new WorkoutGenerator($user);
$workout = $generator->generateWorkout();

echo json_encode([
    'success' => true,
    'message' => 'Тренування згенеровано',
    'workout' => $workout
]);
?>