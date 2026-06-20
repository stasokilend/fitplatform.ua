<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

function updateProfile($userId, $data) {
    global $pdo;
    
    try {
        // Проверяем существование записи
        $stmt = $pdo->prepare("SELECT user_id FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Обновляем существующую запись
            $stmt = $pdo->prepare("
                UPDATE user_profiles 
                SET 
                    age = ?,
                    weight = ?,
                    height = ?,
                    gender = ?,
                    fitness_level = ?,
                    goal_type = ?,
                    target_weight = ?,
                    medical_notes = ?,
                    profile_completed = 1,
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            
            return $stmt->execute([
                $data['age'] ?? null,
                $data['weight'] ?? null,
                $data['height'] ?? null,
                $data['gender'] ?? null,
                $data['fitness_level'] ?? 'beginner',
                $data['goal_type'] ?? 'health',
                $data['target_weight'] ?? null,
                $data['medical_notes'] ?? null,
                $userId
            ]);
        } else {
            // Создаем новую запись
            $stmt = $pdo->prepare("
                INSERT INTO user_profiles (
                    user_id, age, weight, height, gender, 
                    fitness_level, goal_type, target_weight, 
                    medical_notes, profile_completed
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            
            return $stmt->execute([
                $userId,
                $data['age'] ?? null,
                $data['weight'] ?? null,
                $data['height'] ?? null,
                $data['gender'] ?? null,
                $data['fitness_level'] ?? 'beginner',
                $data['goal_type'] ?? 'health',
                $data['target_weight'] ?? null,
                $data['medical_notes'] ?? null
            ]);
        }
    } catch (PDOException $e) {
        error_log('Update profile error: ' . $e->getMessage());
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
        $result = $stmt->fetch();
        
        if (!$result) {
            // Создаем пустой профиль если нет
            $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
            $stmt->execute([$userId]);
            
            $stmt = $pdo->prepare("
                SELECT u.*, up.* 
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log('Get user profile error: ' . $e->getMessage());
        return null;
    }
}

function getDashboardStats($userId) {
    global $pdo;
    
    $stats = [];
    
    try {
        // Всего тренировок
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM workout_plans WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stats['total_workouts'] = $stmt->fetch()['total'] ?? 0;
        
        // Завершенных
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM workout_plans WHERE user_id = ? AND status = 'completed'");
        $stmt->execute([$userId]);
        $stats['completed_workouts'] = $stmt->fetch()['total'] ?? 0;
        
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
        $stats['total_calories'] = $stmt->fetch()['total'] ?? 0;
        
        return $stats;
    } catch (PDOException $e) {
        error_log('Get dashboard stats error: ' . $e->getMessage());
        return $stats;
    }
}
?>