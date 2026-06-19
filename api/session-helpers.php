<?php
// api/session-helpers.php - Допоміжні функції для роботи з тренувальними сесіями

function requireJsonAuth() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
        exit;
    }
    return (int) $_SESSION['user_id'];
}

function getJsonInput() {
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function requireUserSession(Database $db, $sessionId, $userId) {
    $sessionId = filter_var($sessionId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$sessionId) {
        echo json_encode(['success' => false, 'error' => 'Некоректний ID сесії']);
        exit;
    }

    $session = $db->fetchOne(
        'SELECT id FROM training_sessions WHERE id = ? AND user_id = ?',
        [$sessionId, $userId]
    );

    if (!$session) {
        echo json_encode(['success' => false, 'error' => 'Сесію не знайдено']);
        exit;
    }

    return $sessionId;
}
?>
