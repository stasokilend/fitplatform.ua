<?php
// api/admin/update-exercise.php - Оновлення вправи

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
unset($data['id']);

// Формування оновлення
$updates = [];
$params = ['id' => $exerciseId];

$fields = ['name', 'description', 'duration_minutes', 'met_value', 'difficulty', 'video_url', 'equipment'];
foreach ($fields as $field) {
    if (isset($data[$field])) {
        $updates[] = "$field = :$field";
        $params[$field] = $data[$field];
    }
}

// JSON поля
if (isset($data['muscle_groups'])) {
    $updates[] = 'muscle_groups = :muscle_groups';
    $params['muscle_groups'] = json_encode($data['muscle_groups']);
}
if (isset($data['contraindications'])) {
    $updates[] = 'contraindications = :contraindications';
    $params['contraindications'] = json_encode($data['contraindications']);
}

if (empty($updates)) {
    echo json_encode(['success' => false, 'error' => 'Немає даних для оновлення']);
    exit;
}

$sql = "UPDATE exercises SET " . implode(', ', $updates) . " WHERE id = :id";
$db->query($sql, $params);

echo json_encode(['success' => true, 'message' => 'Вправу оновлено']);
?>