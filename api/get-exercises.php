<?php
// api/get-exercises.php - Отримання всіх вправ (для адмінів)

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../classes/Database.php';

$db = Database::getInstance();
$exercises = $db->fetchAll("SELECT * FROM exercises ORDER BY id");

echo json_encode(['success' => true, 'exercises' => $exercises]);
?>