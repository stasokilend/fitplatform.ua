<?php
require_once __DIR__ . '/../config/database.php';

class TrainerController {
    private $pdo;
    private $trainerId;
    
    public function __construct($trainerId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->trainerId = $trainerId;
    }
    
    /**
     * Получение всех клиентов тренера
     */
    public function getClients($status = 'active') {
        $sql = "
            SELECT 
                u.*,
                up.age, up.weight, up.height, up.fitness_level, up.goal_type,
                tc.notes, tc.goals, tc.health_conditions, tc.last_visit,
                tc.assigned_at,
                (SELECT COUNT(*) FROM chat_messages cm
                 JOIN chats c ON cm.chat_id = c.id
                 WHERE (c.user1_id = u.id OR c.user2_id = u.id)
                   AND (c.user1_id = ? OR c.user2_id = ?)
                   AND cm.sender_id = u.id 
                   AND cm.is_read = 0) as unread_messages
            FROM trainer_clients tc
            JOIN users u ON tc.client_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE tc.trainer_id = ? AND tc.status = ?
            ORDER BY u.full_name
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->trainerId, $this->trainerId, $this->trainerId, $status]);
        return $stmt->fetchAll();
    }
    

    /**
     * Получение пользователей, которых тренер может добавить в клиенты.
     */
    public function getAvailableClients($search = '', $limit = 100) {
        $search = trim((string)$search);
        $limit = max(1, min(200, (int)$limit));

        $params = [$this->trainerId];
        $where = "u.role = 'user' AND u.is_active = 1 AND NOT EXISTS (
            SELECT 1 FROM trainer_clients tc
            WHERE tc.trainer_id = ? AND tc.client_id = u.id
        )";

        if ($search !== '') {
            $where .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $stmt = $this->pdo->prepare("
            SELECT u.id, u.full_name, u.email
            FROM users u
            WHERE {$where}
            ORDER BY u.full_name, u.email
            LIMIT {$limit}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Добавление клиента тренеру вместе с созданием чата.
     */
    public function addClient($clientId, $notes = '') {
        $clientId = (int)$clientId;
        $notes = trim((string)$notes);

        if (!$clientId) {
            return ['success' => false, 'error' => 'Виберіть клієнта'];
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id FROM users
                WHERE id = ? AND role = 'user' AND is_active = 1
            ");
            $stmt->execute([$clientId]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'error' => 'Користувача не знайдено або його не можна додати'];
            }

            $stmt = $this->pdo->prepare("
                SELECT 1 FROM trainer_clients
                WHERE trainer_id = ? AND client_id = ?
                LIMIT 1
            ");
            $stmt->execute([$this->trainerId, $clientId]);
            if ($stmt->fetchColumn()) {
                return ['success' => false, 'error' => 'Цей клієнт вже доданий'];
            }

            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO trainer_clients (trainer_id, client_id, notes, status, assigned_at)
                VALUES (?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$this->trainerId, $clientId, $notes]);

            require_once __DIR__ . '/ChatController.php';
            $chat = new ChatController($this->trainerId);
            $chat->getOrCreateChat($clientId);

            $this->pdo->commit();
            return ['success' => true];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Trainer add client error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Помилка додавання клієнта. Спробуйте ще раз.'];
        }
    }

    /**
     * Получение клиента по ID
     */
    public function getClient($clientId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                u.*,
                up.age, up.weight, up.height, up.gender,
                up.fitness_level, up.goal_type, up.target_weight,
                up.medical_notes,
                tc.notes, tc.goals, tc.health_conditions, tc.last_visit,
                tc.assigned_at,
                (SELECT COUNT(*) FROM chat_messages cm
                 JOIN chats c ON cm.chat_id = c.id
                 WHERE (c.user1_id = u.id OR c.user2_id = u.id)
                   AND (c.user1_id = ? OR c.user2_id = ?)
                   AND cm.sender_id = u.id 
                   AND cm.is_read = 0) as unread_messages
            FROM trainer_clients tc
            JOIN users u ON tc.client_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE tc.trainer_id = ? AND tc.client_id = ?
        ");
        $stmt->execute([$this->trainerId, $this->trainerId, $this->trainerId, $clientId]);
        return $stmt->fetch();
    }
    
    /**
     * Получение прогресса клиента
     */
    public function getClientProgress($clientId) {
        // Тренировки клиента
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_workouts,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_workouts,
                SUM(total_duration_min) as total_minutes,
                AVG(total_duration_min) as avg_duration
            FROM workout_plans
            WHERE user_id = ?
        ");
        $stmt->execute([$clientId]);
        $workoutStats = $stmt->fetch();
        
        // Текущая программа
        $stmt = $this->pdo->prepare("
            SELECT 
                cp.*,
                tp.name as program_name,
                tp.duration_weeks,
                tp.sessions_per_week
            FROM client_programs cp
            JOIN trainer_programs tp ON cp.program_id = tp.id
            WHERE cp.client_id = ? AND cp.status = 'active'
            ORDER BY cp.assigned_at DESC
            LIMIT 1
        ");
        $stmt->execute([$clientId]);
        $currentProgram = $stmt->fetch();
        
        // Прогресс веса
        $stmt = $this->pdo->prepare("
            SELECT 
                weight,
                DATE(created_at) as date
            FROM user_profiles_history
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$clientId]);
        $weightHistory = $stmt->fetchAll();
        
        return [
            'workout_stats' => $workoutStats,
            'current_program' => $currentProgram,
            'weight_history' => $weightHistory
        ];
    }
    
    /**
     * Получение программ тренера
     */
    public function getPrograms() {
        $stmt = $this->pdo->prepare("
            SELECT 
                tp.*,
                (SELECT COUNT(*) FROM program_exercises WHERE program_id = tp.id) as exercises_count,
                (SELECT COUNT(*) FROM client_programs WHERE program_id = tp.id) as clients_count
            FROM trainer_programs tp
            WHERE tp.trainer_id = ?
            ORDER BY tp.created_at DESC
        ");
        $stmt->execute([$this->trainerId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Создание программы
     */
    public function createProgram($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO trainer_programs (trainer_id, name, description, difficulty, duration_weeks, sessions_per_week, is_public)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $this->trainerId,
            $data['name'],
            $data['description'],
            $data['difficulty'],
            $data['duration_weeks'],
            $data['sessions_per_week'],
            $data['is_public'] ?? 0
        ]);
        
        if ($success) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }
    
    /**
     * Добавление упражнения в программу
     */
    public function addExerciseToProgram($programId, $exerciseId, $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO program_exercises (program_id, exercise_id, day, sets, reps, rest_seconds, notes, order_num)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $programId,
            $exerciseId,
            $data['day'] ?? 1,
            $data['sets'] ?? 3,
            $data['reps'] ?? 10,
            $data['rest_seconds'] ?? 60,
            $data['notes'] ?? null,
            $data['order_num'] ?? 0
        ]);
    }
    
    /**
     * Назначение программы клиенту
     */
    public function assignProgramToClient($clientId, $programId, $startDate) {
        $stmt = $this->pdo->prepare("
            INSERT INTO client_programs (client_id, program_id, trainer_id, start_date, status)
            VALUES (?, ?, ?, ?, 'active')
        ");
        
        return $stmt->execute([$clientId, $programId, $this->trainerId, $startDate]);
    }
    
    /**
     * Создание записи в расписании
     */
    public function addSchedule($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO trainer_schedule (trainer_id, client_id, day_of_week, start_time, end_time, date, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $this->trainerId,
            $data['client_id'] ?? null,
            $data['day_of_week'],
            $data['start_time'],
            $data['end_time'],
            $data['date'] ?? null,
            $data['notes'] ?? null
        ]);
    }
    
    /**
     * Получение расписания на неделю
     */
    public function getSchedule($weekStart) {
        $stmt = $this->pdo->prepare("
            SELECT 
                ts.*,
                u.full_name as client_name
            FROM trainer_schedule ts
            LEFT JOIN users u ON ts.client_id = u.id
            WHERE ts.trainer_id = ? 
              AND ts.date BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY)
            ORDER BY ts.date, ts.start_time
        ");
        $stmt->execute([$this->trainerId, $weekStart, $weekStart]);
        return $stmt->fetchAll();
    }
    
    /**
     * Проверка, есть ли у тренера активные клиенты
     */
    public function hasActiveClients() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM trainer_clients 
            WHERE trainer_id = ? AND status = 'active'
        ");
        $stmt->execute([$this->trainerId]);
        return ($stmt->fetch()['count'] ?? 0) > 0;
    }
    
    /**
     * Изменение роли тренера на обычного пользователя
     */
    public function switchToUser() {
        try {
            if ($this->hasActiveClients()) {
                return [
                    'success' => false, 
                    'error' => 'У вас є активні клієнти. Спочатку передайте їх іншому тренеру.'
                ];
            }
            
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
            $success = $stmt->execute([$this->trainerId]);
            
            if ($success) {
                $stmt = $this->pdo->prepare("UPDATE trainer_programs SET is_active = 0 WHERE trainer_id = ?");
                $stmt->execute([$this->trainerId]);
                
                $stmt = $this->pdo->prepare("UPDATE trainer_clients SET status = 'inactive' WHERE trainer_id = ?");
                $stmt->execute([$this->trainerId]);
                
                $this->pdo->commit();
                $_SESSION['user_role'] = 'user';
                return ['success' => true];
            }
            
            $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Помилка зміни ролі'];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Помилка: ' . $e->getMessage()];
        }
    }
    
    /**
 * Изменение роли обычного пользователя на тренера
 */
public function switchToTrainer() {
    try {
        // Проверяем, не является ли уже тренером
        $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$this->trainerId]);
        $user = $stmt->fetch();
        
        if ($user['role'] === 'trainer') {
            return ['success' => false, 'error' => 'Ви вже є тренером'];
        }
        
        // Меняем роль
        $stmt = $this->pdo->prepare("
            UPDATE users SET role = 'trainer' WHERE id = ?
        ");
        $success = $stmt->execute([$this->trainerId]);
        
        if ($success) {
            // Обновляем сессию
            $_SESSION['user_role'] = 'trainer';
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Помилка зміни ролі'];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Помилка: ' . $e->getMessage()];
    }
}
    
    public function getActiveClientsCount() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM trainer_clients 
            WHERE trainer_id = ? AND status = 'active'
        ");
        $stmt->execute([$this->trainerId]);
        return (int)($stmt->fetch()['count'] ?? 0);
    }
    
    public function transferClients($newTrainerId) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'trainer'");
        $stmt->execute([$newTrainerId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'error' => 'Тренера не знайдено'];
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE trainer_clients 
            SET trainer_id = ? 
            WHERE trainer_id = ? AND status = 'active'
        ");
        $success = $stmt->execute([$newTrainerId, $this->trainerId]);
        return ['success' => $success];
    }
    
    public function getAvailableTrainers() {
        $stmt = $this->pdo->prepare("
            SELECT id, full_name, email, specialty 
            FROM users 
            WHERE role = 'trainer' AND id != ? AND is_active = 1
            ORDER BY full_name
        ");
        $stmt->execute([$this->trainerId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение статистики тренера
     */
    public function getStats() {
        $stats = [];
        
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM trainer_clients 
            WHERE trainer_id = ? AND status = 'active'
        ");
        $stmt->execute([$this->trainerId]);
        $stats['active_clients'] = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM trainer_programs 
            WHERE trainer_id = ? AND is_active = 1
        ");
        $stmt->execute([$this->trainerId]);
        $stats['programs'] = $stmt->fetch()['total'] ?? 0;
        
        // Непрочитанные сообщения в чатах
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total 
            FROM chat_messages cm
            JOIN chats c ON cm.chat_id = c.id
            WHERE (c.user1_id = ? OR c.user2_id = ?) 
              AND cm.sender_id != ? 
              AND cm.is_read = 0
        ");
        $stmt->execute([$this->trainerId, $this->trainerId, $this->trainerId]);
        $stats['unread_messages'] = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM trainer_schedule 
            WHERE trainer_id = ? AND date = CURDATE() AND status = 'booked'
        ");
        $stmt->execute([$this->trainerId]);
        $stats['today_sessions'] = $stmt->fetch()['total'] ?? 0;
        
        return $stats;
    }
}
?>