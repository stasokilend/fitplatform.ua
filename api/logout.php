<?php
// api/logout.php - Вихід з системи

session_start();
session_destroy();
echo json_encode(['success' => true]);
?>