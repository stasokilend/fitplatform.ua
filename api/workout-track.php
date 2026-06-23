<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'complete') {
    $exerciseId = (int)($_POST['exercise_id'] ?? 0);
    $planId = (int)($_POST['plan_id'] ?? 0);
    
    if (!$exerciseId || !$planId) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    // Проверяем, что план принадлежит пользователю
    $stmt = $pdo->prepare("SELECT id, status FROM workout_plans WHERE id = ? AND user_id = ?");
    $stmt->execute([$planId, $userId]);
    $plan = $stmt->fetch();
    if (!$plan) {
        echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
        exit;
    }
    
    // Отмечаем упражнение как выполненное
    $stmt = $pdo->prepare("
        UPDATE plan_exercises 
        SET is_completed = 1 
        WHERE id = ? AND plan_id = ?
    ");
    $success = $stmt->execute([$exerciseId, $planId]);
    
    echo json_encode(['success' => $success]);
    exit;
}

if ($action === 'finish') {
    $planId = (int)($_POST['plan_id'] ?? 0);
    
    if (!$planId) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    // Проверяем, что план принадлежит пользователю
    $stmt = $pdo->prepare("SELECT id FROM workout_plans WHERE id = ? AND user_id = ?");
    $stmt->execute([$planId, $userId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
        exit;
    }
    
    // Обновляем статус плана
    $stmt = $pdo->prepare("
        UPDATE workout_plans 
        SET status = 'completed', completed_at = NOW()
        WHERE id = ? AND status <> 'completed'
    ");
    $stmt->execute([$planId]);
    $success = $stmt->rowCount() > 0;
    
    // Добавляем лог
    if ($success) {
        $stmt = $pdo->prepare("
            INSERT INTO workout_logs (user_id, plan_id, ended_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$userId, $planId]);
    }
    
    // Обновляем геймификацию и достижения после завершения плана.
    if ($success) {
        require_once __DIR__ . '/../controllers/GamificationController.php';
        
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(pe.id) as total,
                COUNT(CASE WHEN pe.is_completed = 1 THEN 1 END) as completed,
                SUM(CASE WHEN pe.is_completed = 1 THEN COALESCE(e.calories_per_min, 0) * COALESCE(e.duration_min, 0) ELSE 0 END) as calories
            FROM plan_exercises pe
            JOIN exercises e ON pe.exercise_id = e.id
            WHERE pe.plan_id = ?
        ");
        $stmt->execute([$planId]);
        $data = $stmt->fetch();
        
        $gamification = new GamificationController($userId);
        $gamification->recordWorkoutCompleted(
            $planId,
            (int)round($data['calories'] ?? 0),
            (int)($data['completed'] ?? 0),
            (int)($data['total'] ?? 0)
        );
    }

    echo json_encode(['success' => $success]);
    exit;

    // --- РАСЧЕТ КАЛОРИЙ ДЛЯ ТЕКУЩЕЙ ТРЕНИРОВКИ ---
if ($action === 'calculate_calories') {
    $workoutId = (int)($_POST['workout_id'] ?? 0);
    if (!$workoutId) {
        echo json_encode(['success' => false, 'error' => 'ID не вказано']);
        exit;
    }
    
    $exercises = $workout->getWorkoutExercises($workoutId);
    $totalCalories = 0;
    
    foreach ($exercises as $exercise) {
        if ($exercise['is_completed']) {
            $caloriesPerMin = (float)($exercise['calories_per_min'] ?? 0);
            $durationMin = (float)($exercise['duration_min'] ?? 0);
            $sets = (int)($exercise['sets'] ?? 1);
            
            if ($durationMin > 0 && $caloriesPerMin > 0) {
                $totalCalories += $caloriesPerMin * $durationMin * $sets;
            } else {
                $totalCalories += 5; // Fallback
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'calories' => (int)round($totalCalories)
    ]);
    exit;
}
}

echo json_encode(['success' => false, 'error' => 'Невідома дія']);


?>