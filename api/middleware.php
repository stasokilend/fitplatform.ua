<?php
// api/middleware.php

function requireAuth() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Не авторизований']);
        exit;
    }
    return $_SESSION['user_id'];
}

function requireRole($allowedRoles = ['admin', 'trainer']) {
    $userId = requireAuth();
    require_once __DIR__ . '/../classes/Database.php';
    $db = Database::getInstance();
    $user = $db->fetchOne("SELECT role FROM users WHERE id = ?", [$userId]);
    if (!$user || !in_array($user['role'], $allowedRoles)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
        exit;
    }
    return $user;
}