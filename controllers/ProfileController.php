<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

function updateProfile($userId, $data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE user_profiles 
            SET age = ?, 
                weight = ?, 
                height = ?, 
                gender = ?, 
                fitness_level = ?, 
                goal_type = ?, 
                target_weight = ?,
                medical_notes = ?, 
                profile_completed = 1
            WHERE user_id = ?
        ");
        
        $result = $stmt->execute([
            $data['age'],
            $data['weight'],
            $data['height'],
            $data['gender'],
            $data['fitness_level'],
            $data['goal_type'],
            $data['target_weight'] ?? null,
            $data['medical_notes'] ?? null,
            $userId
        ]);
        
        error_log("Profile update for user $userId: " . ($result ? 'success' : 'failed'));
        
        return $result;
    } catch (Exception $e) {
        error_log("Error updating profile: " . $e->getMessage());
        return false;
    }
}

function getUserProfile($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, up.* 
            FROM users u
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting profile: " . $e->getMessage());
        return null;
    }
}

function getDashboardStats($userId) {
    global $pdo;
    
    $stats = [
        'total_workouts' => 0,
        'completed_workouts' => 0,
        'total_calories' => 0,
        'last_workout' => null
    ];
    
    try {
        // Всего тренировок
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM workout_plans WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stats['total_workouts'] = (int)($stmt->fetch()['total'] ?? 0);
        
        // Завершенных
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM workout_plans WHERE user_id = ? AND status = 'completed'");
        $stmt->execute([$userId]);
        $stats['completed_workouts'] = (int)($stmt->fetch()['total'] ?? 0);
        
        // Последняя тренировка
        $stmt = $pdo->prepare("
            SELECT * FROM workout_plans 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $stats['last_workout'] = $stmt->fetch();
        
        // Всего сожжено калорий
        $stmt = $pdo->prepare("SELECT SUM(calories_burned) as total FROM workout_logs WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stats['total_calories'] = (float)($stmt->fetch()['total'] ?? 0);
        
    } catch (Exception $e) {
        error_log("Error getting stats: " . $e->getMessage());
    }
    
    return $stats;
}
?>