<?php
require_once __DIR__ . '/../../config/database.php';

// Получение всех пользователей
function getAllUsers($limit = 50, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.*, up.fitness_level, up.goal_type, up.profile_completed
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.user_id
        ORDER BY u.created_at DESC
        LIMIT " . (int)$limit . " OFFSET " . (int)$offset . "
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Получение пользователя по ID
function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.*, up.* 
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Обновление пользователя
function updateUser($id, $data) {
    global $pdo;
    
    $fields = [];
    $params = [];
    
    if (isset($data['full_name'])) {
        $fields[] = "full_name = ?";
        $params[] = $data['full_name'];
    }
    if (isset($data['role'])) {
        $fields[] = "role = ?";
        $params[] = $data['role'];
    }
    if (isset($data['is_active'])) {
        $fields[] = "is_active = ?";
        $params[] = $data['is_active'];
    }
    if (!empty($data['password'])) {
        $fields[] = "password_hash = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $params[] = $id;
    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// Удаление пользователя
function deleteUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    return $stmt->execute([$id]);
}

// Получение всех упражнений
function getAllExercises() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM exercises ORDER BY muscle_group, name");
    return $stmt->fetchAll();
}

// Получение упражнения по ID
function getExerciseById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM exercises WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Добавление упражнения
function addExercise($data) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO exercises (name, description, muscle_group, equipment, difficulty, 
                              duration_min, calories_per_min, video_url, image_url, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $data['name'],
        $data['description'],
        $data['muscle_group'],
        $data['equipment'],
        $data['difficulty'],
        $data['duration_min'],
        $data['calories_per_min'],
        $data['video_url'] ?? null,
        $data['image_url'] ?? null,
        $data['is_active'] ?? 1
    ]);
}

// Обновление упражнения
function updateExercise($id, $data) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE exercises 
        SET name = ?, description = ?, muscle_group = ?, equipment = ?, 
            difficulty = ?, duration_min = ?, calories_per_min = ?, 
            video_url = ?, image_url = ?, is_active = ?
        WHERE id = ?
    ");
    
    return $stmt->execute([
        $data['name'],
        $data['description'],
        $data['muscle_group'],
        $data['equipment'],
        $data['difficulty'],
        $data['duration_min'],
        $data['calories_per_min'],
        $data['video_url'] ?? null,
        $data['image_url'] ?? null,
        $data['is_active'] ?? 1,
        $id
    ]);
}

// Удаление упражнения
function deleteExercise($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM exercises WHERE id = ?");
    return $stmt->execute([$id]);
}

// Получение медицинских ограничений
function getAllRestrictions() {
    global $pdo;
    return $pdo->query("SELECT * FROM medical_restrictions ORDER BY name")->fetchAll();
}

// Добавление ограничения
function addRestriction($name) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO medical_restrictions (name) VALUES (?)");
    return $stmt->execute([$name]);
}

// Удаление ограничения
function deleteRestriction($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM medical_restrictions WHERE id = ?");
    return $stmt->execute([$id]);
}

// ИСПРАВЛЕННАЯ ФУНКЦИЯ - Получение последних действий
function getRecentActivity($limit = 10) {
    global $pdo;
    
    $limit = (int)$limit;
    $activity = [];
    
    // Регистрации
    $stmt = $pdo->prepare("
        SELECT 'registration' as type, u.id, u.full_name, u.created_at as time 
        FROM users u 
        ORDER BY u.created_at DESC 
        LIMIT " . $limit . "
    ");
    $stmt->execute();
    $registrations = $stmt->fetchAll();
    
    // Тренировки
    $stmt = $pdo->prepare("
        SELECT 'workout' as type, w.id, u.full_name, w.created_at as time 
        FROM workout_plans w
        JOIN users u ON w.user_id = u.id
        ORDER BY w.created_at DESC 
        LIMIT " . $limit . "
    ");
    $stmt->execute();
    $workouts = $stmt->fetchAll();
    
    // Активность пульса (логины/активность)
    $stmt = $pdo->prepare("
        SELECT 'login' as type, l.user_id as id, u.full_name, l.recorded_at as time 
        FROM heart_rate_logs l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.recorded_at DESC 
        LIMIT " . $limit . "
    ");
    $stmt->execute();
    $logins = $stmt->fetchAll();
    
    // Объединяем все
    $activity = array_merge($registrations, $workouts, $logins);
    
    // Сортируем по времени (новые сверху)
    usort($activity, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
    // Ограничиваем количество
    return array_slice($activity, 0, $limit);
}

// Получение статистики для графиков
function getMonthlyStats($months = 6) {
    global $pdo;
    
    $stats = [];
    
    for ($i = $months - 1; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $dateStart = $month . '-01';
        $dateEnd = date('Y-m-t', strtotime($dateStart));
        
        // Новые пользователи
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM users 
            WHERE created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$dateStart, $dateEnd]);
        $newUsers = $stmt->fetch()['count'] ?? 0;
        
        // Новые тренировки
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM workout_plans 
            WHERE created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$dateStart, $dateEnd]);
        $newWorkouts = $stmt->fetch()['count'] ?? 0;
        
        $stats[] = [
            'month' => $month,
            'new_users' => $newUsers,
            'new_workouts' => $newWorkouts
        ];
    }
    
    return $stats;
}

// Получение статистики за последние 7 дней
function getWeeklyStats() {
    global $pdo;
    
    $stats = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        
        // Регистрации за день
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM users 
            WHERE DATE(created_at) = ?
        ");
        $stmt->execute([$date]);
        $newUsers = $stmt->fetch()['count'] ?? 0;
        
        // Тренировки за день
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM workout_plans 
            WHERE DATE(created_at) = ?
        ");
        $stmt->execute([$date]);
        $newWorkouts = $stmt->fetch()['count'] ?? 0;
        
        $stats[] = [
            'date' => $date,
            'new_users' => $newUsers,
            'new_workouts' => $newWorkouts
        ];
    }
    
    return $stats;
}
?>