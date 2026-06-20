<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/GamificationController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$gamification = new GamificationController($userId);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- ПОЛУЧЕНИЕ СТАТИСТИКИ ---
if ($action === 'stats' || !$action) {
    $stats = $gamification->getGamificationStats();
    echo json_encode(['success' => true, 'data' => $stats]);
    exit;
}

// --- ПОЛУЧЕНИЕ ДОСТИЖЕНИЙ ---
if ($action === 'achievements') {
    $achievements = $gamification->getUserAchievements();
    echo json_encode(['success' => true, 'data' => $achievements]);
    exit;
}

// --- ПОЛУЧЕНИЕ ПОСЛЕДНИХ ДОСТИЖЕНИЙ ---
if ($action === 'recent') {
    $limit = (int)($_GET['limit'] ?? 5);
    $recent = $gamification->getRecentAchievements($limit);
    echo json_encode(['success' => true, 'data' => $recent]);
    exit;
}

// --- ОБНОВЛЕНИЕ СТАТИСТИКИ ПОСЛЕ ТРЕНИРОВКИ ---
if ($action === 'update_workout') {
    $calories = (int)($_POST['calories'] ?? 0);
    $exercisesCompleted = (int)($_POST['exercises'] ?? 0);
    $isComplete = $_POST['is_complete'] ?? true;
    
    $gamification->updateStats($calories, $exercisesCompleted, $isComplete);
    
    echo json_encode(['success' => true]);
    exit;
}

// --- РАЗБЛОКИРОВКА ДОСТИЖЕНИЯ ---
if ($action === 'unlock') {
    $achievementCode = $_POST['code'] ?? '';
    
    if (!$achievementCode) {
        echo json_encode(['success' => false, 'error' => 'Код не вказано']);
        exit;
    }
    
    $result = $gamification->unlockAchievement($achievementCode);
    
    if ($result) {
        // Получаем детали достижения для уведомления
        $stmt = $pdo->prepare("SELECT * FROM achievements WHERE code = ?");
        $stmt->execute([$achievementCode]);
        $achievement = $stmt->fetch();
        
        // Создаем уведомление
        require_once __DIR__ . '/../controllers/NotificationController.php';
        $notification = new NotificationController($userId);
        $notification->createFromTemplate('achievement_unlocked', [
            'achievement_name' => $achievement['name']
        ]);
        
        echo json_encode([
            'success' => true,
            'achievement' => $achievement
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Помилка розблокування']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Невідома дія']);
?>