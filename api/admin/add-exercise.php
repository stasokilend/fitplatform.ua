<?php
// api/admin/add-exercise.php - Додавання нової вправи

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../classes/Database.php';

// Перевірка прав (адмін або тренер)
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

$required = ['name', 'duration_minutes', 'met_value', 'difficulty'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'error' => "Поле '$field' обов'язкове"]);
        exit;
    }
}

// Підготовка даних
$exerciseData = [
    'name' => $data['name'],
    'description' => $data['description'] ?? '',
    'duration_minutes' => $data['duration_minutes'],
    'met_value' => $data['met_value'],
    'muscle_groups' => json_encode($data['muscle_groups'] ?? []),
    'difficulty' => $data['difficulty'],
    'contraindications' => json_encode($data['contraindications'] ?? []),
    'video_url' => $data['video_url'] ?? null,
    'equipment' => $data['equipment'] ?? null
];

$id = $db->insert('exercises', $exerciseData);

echo json_encode(['success' => true, 'message' => 'Вправу додано', 'exercise_id' => $id]);
?>