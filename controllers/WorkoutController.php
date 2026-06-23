<?php
require_once __DIR__ . '/../config/database.php';

class WorkoutController {
    private $pdo;
    private $userId;
    
    public function __construct($userId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
    }
    
    /**
     * Получение всех тренировок пользователя
     */
    public function getUserWorkouts($limit = 50, $offset = 0, $status = null) {
        // Убеждаемся, что offset не отрицательный
        $offset = max(0, (int)$offset);
        $limit = max(1, (int)$limit);
        
        $sql = "
            SELECT w.*, 
                   COUNT(pe.id) as total_exercises,
                   COUNT(CASE WHEN pe.is_completed = 1 THEN 1 END) as completed_exercises,
                   (SELECT COUNT(*) FROM workout_notes WHERE workout_id = w.id) as notes_count
            FROM workout_plans w
            LEFT JOIN plan_exercises pe ON w.id = pe.plan_id
            WHERE w.user_id = ?
        ";
        
        $params = [$this->userId];
        
        if ($status) {
            $sql .= " AND w.status = ?";
            $params[] = $status;
        }
        
        $sql .= " GROUP BY w.id ORDER BY w.created_at DESC LIMIT " . $limit . " OFFSET " . $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение тренировки по ID
     */
    public function getWorkout($workoutId) {
        $stmt = $this->pdo->prepare("
            SELECT w.*,
                   COUNT(pe.id) as total_exercises,
                   COUNT(CASE WHEN pe.is_completed = 1 THEN 1 END) as completed_exercises
            FROM workout_plans w
            LEFT JOIN plan_exercises pe ON w.id = pe.plan_id
            WHERE w.id = ? AND w.user_id = ?
            GROUP BY w.id
        ");
        $stmt->execute([$workoutId, $this->userId]);
        return $stmt->fetch();
    }
    
    /**
     * Получение упражнений тренировки
     */
    public function getWorkoutExercises($workoutId) {
        $stmt = $this->pdo->prepare("
            SELECT pe.*, e.name, e.muscle_group, e.difficulty, e.description, e.calories_per_min
            FROM plan_exercises pe
            JOIN exercises e ON pe.exercise_id = e.id
            WHERE pe.plan_id = ?
            ORDER BY pe.order_num
        ");
        $stmt->execute([$workoutId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Создание тренировки
     */
    public function createWorkout($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO workout_plans (user_id, name, total_duration_min, difficulty, focus, status)
            VALUES (?, ?, ?, ?, ?, 'planned')
        ");
        
        $success = $stmt->execute([
            $this->userId,
            $data['name'],
            $data['duration'],
            $data['difficulty'] ?? 'intermediate',
            $data['focus'] ?? 'general'
        ]);
        
        if ($success) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }
    
    /**
     * Добавление упражнения в тренировку
     */
    public function addExercise($workoutId, $exerciseId, $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO plan_exercises (plan_id, exercise_id, sets, reps, weight_kg, order_num)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $workoutId,
            $exerciseId,
            $data['sets'] ?? 3,
            $data['reps'] ?? 10,
            $data['weight'] ?? null,
            $data['order'] ?? 0
        ]);
    }
    
    /**
     * Обновление статуса упражнения
     */
    public function toggleExerciseComplete($exerciseId) {
        $stmt = $this->pdo->prepare("
            UPDATE plan_exercises 
            SET is_completed = NOT is_completed 
            WHERE id = ? AND plan_id IN (
                SELECT id FROM workout_plans WHERE user_id = ?
            )
        ");
        return $stmt->execute([$exerciseId, $this->userId]);
    }
    
    /**
     * Завершение тренировки
     */
    public function completeWorkout($workoutId) {
        $stmt = $this->pdo->prepare("
            UPDATE workout_plans 
            SET status = 'completed', completed_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$workoutId, $this->userId]);
    }
    

    /**
     * Удаление тренировки пользователя.
     */
    public function deleteWorkout($workoutId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM workout_plans
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([(int)$workoutId, $this->userId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Добавление заметки к тренировке
     */
    public function addNote($workoutId, $note) {
        $stmt = $this->pdo->prepare("
            INSERT INTO workout_notes (workout_id, user_id, note)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$workoutId, $this->userId, $note]);
    }
    
    /**
     * Получение заметок к тренировке
     */
    public function getNotes($workoutId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM workout_notes 
            WHERE workout_id = ? AND user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$workoutId, $this->userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение статистики тренировок
     */
    public function getWorkoutStats() {
        $stats = [];
        
        // Всего тренировок
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM workout_plans WHERE user_id = ?
        ");
        $stmt->execute([$this->userId]);
        $stats['total'] = $stmt->fetch()['total'] ?? 0;
        
        // Завершенных
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM workout_plans 
            WHERE user_id = ? AND status = 'completed'
        ");
        $stmt->execute([$this->userId]);
        $stats['completed'] = $stmt->fetch()['total'] ?? 0;
        
        // В процессе
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM workout_plans 
            WHERE user_id = ? AND status = 'in_progress'
        ");
        $stmt->execute([$this->userId]);
        $stats['in_progress'] = $stmt->fetch()['total'] ?? 0;
        
        // Запланированных
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM workout_plans 
            WHERE user_id = ? AND status = 'planned'
        ");
        $stmt->execute([$this->userId]);
        $stats['planned'] = $stmt->fetch()['total'] ?? 0;
        
        // Всего калорий
        $stmt = $this->pdo->prepare("
            SELECT SUM(calories_burned) as total FROM workout_plans 
            WHERE user_id = ? AND status = 'completed'
        ");
        $stmt->execute([$this->userId]);
        $stats['total_calories'] = $stmt->fetch()['total'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Получение шаблонов тренировок
     */
    public function getTemplates($limit = 10) {
        $limit = max(1, (int)$limit);
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM workout_templates 
            WHERE is_public = 1 OR created_by = ?
            ORDER BY created_at DESC
            LIMIT " . $limit . "
        ");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение упражнений шаблона
     */
    public function getTemplateExercises($templateId) {
        $stmt = $this->pdo->prepare("
            SELECT te.*, e.name, e.muscle_group, e.difficulty
            FROM template_exercises te
            JOIN exercises e ON te.exercise_id = e.id
            WHERE te.template_id = ?
            ORDER BY te.order_num
        ");
        $stmt->execute([$templateId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Резервный набор упражнений для шаблона без привязанных упражнений.
     */
    private function getFallbackTemplateExercises($difficulty, $focus) {
        $focusGroups = [
            'strength' => ['Ноги', 'Груди', 'Кор'],
            'cardio' => ['Все тіло', 'Ноги', 'Кор'],
            'functional' => ['Ноги', 'Все тіло', 'Кор'],
            'core' => ['Кор', 'Прес'],
            'flexibility' => ['Кор', 'Прес'],
        ];
        $groups = $focusGroups[$focus] ?? ['Ноги', 'Груди', 'Кор', 'Все тіло', 'Прес'];
        $placeholders = implode(',', array_fill(0, count($groups), '?'));

        $stmt = $this->pdo->prepare("
            SELECT id as exercise_id, 3 as sets, 10 as reps, 0 as order_num
            FROM exercises
            WHERE is_active = 1
              AND (difficulty = ? OR muscle_group IN ($placeholders))
            ORDER BY FIELD(muscle_group, $placeholders), difficulty, name
            LIMIT 5
        ");
        $stmt->execute(array_merge([$difficulty], $groups, $groups));
        return $stmt->fetchAll();
    }

    /**
     * Создание тренировки из шаблона
     */
    public function createFromTemplate($templateId, $name = null) {
        // Получаем шаблон
        $stmt = $this->pdo->prepare("SELECT * FROM workout_templates WHERE id = ?");
        $stmt->execute([$templateId]);
        $template = $stmt->fetch();
        
        if (!$template) {
            return false;
        }
        
        // Создаем тренировку
        $workoutData = [
            'name' => $name ?? $template['name'],
            'duration' => $template['duration_min'],
            'difficulty' => $template['difficulty'],
            'focus' => $template['focus']
        ];
        
        $workoutId = $this->createWorkout($workoutData);
        
        if (!$workoutId) {
            return false;
        }
        
        // Добавляем упражнения из шаблона. Если у старой базы нет строк
        // template_exercises, берём подходящие активные упражнения, чтобы
        // пользователь не попадал в пустую тренировку.
        $exercises = $this->getTemplateExercises($templateId);
        if (empty($exercises)) {
            $exercises = $this->getFallbackTemplateExercises($template['difficulty'], $template['focus']);
        }

        foreach ($exercises as $index => $ex) {
            $this->addExercise($workoutId, $ex['exercise_id'], [
                'sets' => $ex['sets'] ?? 3,
                'reps' => $ex['reps'] ?? 10,
                'order' => $ex['order_num'] ?? $index
            ]);
        }
        
        return $workoutId;
    }
}
?>