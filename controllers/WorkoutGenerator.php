<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Класс для генерации персонализированных тренировок
 */
class WorkoutGenerator {
    private $pdo;
    private $userId;
    private $profile;
    private $exercises;
    
    public function __construct($userId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->loadProfile();
        $this->loadExercises();
    }
    
    private function loadProfile() {
        $stmt = $this->pdo->prepare("
            SELECT u.*, up.* 
            FROM users u
            JOIN user_profiles up ON u.id = up.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$this->userId]);
        $this->profile = $stmt->fetch();
    }
    
    private function loadExercises() {
        // Получаем ограничения пользователя
        $stmt = $this->pdo->prepare("
            SELECT restriction_id FROM user_restrictions WHERE user_id = ?
        ");
        $stmt->execute([$this->userId]);
        $restrictions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Строим запрос с исключением запрещенных упражнений
        $sql = "SELECT * FROM exercises WHERE is_active = 1";
        
        if (!empty($restrictions)) {
            $placeholders = implode(',', array_fill(0, count($restrictions), '?'));
            $sql .= " AND id NOT IN (
                SELECT exercise_id FROM exercise_restrictions 
                WHERE restriction_id IN ($placeholders)
            )";
        }
        
        $sql .= " ORDER BY muscle_group, name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($restrictions);
        $this->exercises = $stmt->fetchAll();
    }
    
    public function generateWorkout($duration = 30, $focus = null) {
        $selectedExercises = [];
        $totalDuration = 0;
        $totalCalories = 0;
        
        if (!$focus) {
            $focus = $this->profile['goal_type'] ?? 'health';
        }
        
        $available = $this->getPrioritizedExercises($focus);
        
        $levelMap = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3];
        $userLevel = $levelMap[$this->profile['fitness_level'] ?? 'beginner'] ?? 1;
        
        foreach ($available as $exercise) {
            if ($totalDuration + $exercise['duration_min'] > $duration) {
                continue;
            }
            
            $exerciseLevel = $levelMap[$exercise['difficulty']] ?? 1;
            if ($exerciseLevel > $userLevel + 1) {
                continue;
            }
            
            // Добавляем sets и reps для отображения
            $exercise['sets'] = $this->calculateSets($exercise);
            $exercise['reps'] = $this->calculateReps($exercise);
            
            $selectedExercises[] = $exercise;
            $totalDuration += $exercise['duration_min'];
            $totalCalories += $exercise['calories_per_min'] * $exercise['duration_min'];
            
            if ($totalDuration >= $duration) {
                break;
            }
        }
        
        if (count($selectedExercises) < 3) {
            $simple = $this->getSimpleExercises($duration - $totalDuration, $userLevel);
            foreach ($simple as $ex) {
                $ex['sets'] = $this->calculateSets($ex);
                $ex['reps'] = $this->calculateReps($ex);
            }
            $selectedExercises = array_merge($selectedExercises, $simple);
        }
        
        return [
            'exercises' => $selectedExercises,
            'total_duration' => round($totalDuration, 1),
            'total_calories' => round($totalCalories),
            'focus' => $focus,
            'count' => count($selectedExercises)
        ];
    }
    
    private function getPrioritizedExercises($focus) {
        $priorities = [];
        
        foreach ($this->exercises as $ex) {
            $score = 0;
            
            switch ($focus) {
                case 'weight_loss':
                    $score = $ex['calories_per_min'] * 2;
                    break;
                case 'muscle_gain':
                    $score = ($ex['difficulty'] === 'advanced' ? 3 : 1);
                    $muscleScore = [
                        'Ноги' => 2,
                        'Груди' => 2,
                        'Спина' => 2,
                        'Плечі' => 1.5,
                        'Руки' => 1.5,
                        'Прес' => 1,
                        'Все тіло' => 3
                    ];
                    $score += $muscleScore[$ex['muscle_group']] ?? 1;
                    break;
                case 'endurance':
                    $score = $ex['duration_min'] * 0.5 + ($ex['difficulty'] === 'beginner' ? 2 : 0);
                    break;
                default:
                    $score = 1 / ($ex['duration_min'] + 1);
                    break;
            }
            
            $priorities[] = [
                'exercise' => $ex,
                'score' => $score
            ];
        }
        
        usort($priorities, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return array_column($priorities, 'exercise');
    }
    
    private function getSimpleExercises($remainingTime, $userLevel) {
        $simple = [];
        $time = 0;
        $levelMap = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3];
        
        foreach ($this->exercises as $ex) {
            $exLevel = $levelMap[$ex['difficulty']] ?? 1;
            if ($exLevel <= $userLevel + 1 && $time + $ex['duration_min'] <= $remainingTime) {
                $simple[] = $ex;
                $time += $ex['duration_min'];
            }
        }
        
        return $simple;
    }
    
    private function calculateSets($exercise) {
        $levelMap = ['beginner' => 3, 'intermediate' => 4, 'advanced' => 5];
        $level = $this->profile['fitness_level'] ?? 'beginner';
        $base = $levelMap[$level] ?? 3;
        
        if ($exercise['duration_min'] > 3) {
            return max(2, $base - 1);
        }
        return $base;
    }
    
    private function calculateReps($exercise) {
        $levelMap = ['beginner' => 10, 'intermediate' => 12, 'advanced' => 15];
        $level = $this->profile['fitness_level'] ?? 'beginner';
        $base = $levelMap[$level] ?? 10;
        
        if ($exercise['difficulty'] === 'advanced') {
            return max(6, $base - 4);
        }
        return $base;
    }
    
    public function saveWorkout($name, $exercises, $duration) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO workout_plans (user_id, name, total_duration_min, status)
                VALUES (?, ?, ?, 'planned')
            ");
            $stmt->execute([$this->userId, $name, $duration]);
            $planId = $this->pdo->lastInsertId();
            
            $order = 0;
            foreach ($exercises as $ex) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO plan_exercises (plan_id, exercise_id, sets, reps, order_num)
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $sets = $ex['sets'] ?? $this->calculateSets($ex);
                $reps = $ex['reps'] ?? $this->calculateReps($ex);
                
                $stmt->execute([
                    $planId,
                    $ex['id'],
                    $sets,
                    $reps,
                    $order++
                ]);
            }
            
            return $planId;
        } catch (Exception $e) {
            error_log('Save workout error: ' . $e->getMessage());
            return false;
        }
    }
}

function generateWorkoutForUser($userId, $duration = 30, $focus = null) {
    $generator = new WorkoutGenerator($userId);
    return $generator->generateWorkout($duration, $focus);
}

function createWorkoutFromGeneration($userId, $name, $duration = 30, $focus = null) {
    $generator = new WorkoutGenerator($userId);
    $result = $generator->generateWorkout($duration, $focus);
    
    if (empty($result['exercises'])) {
        return ['success' => false, 'error' => 'Не вдалося підібрати вправи'];
    }
    
    $planId = $generator->saveWorkout($name, $result['exercises'], $result['total_duration']);
    
    if ($planId) {
        return [
            'success' => true,
            'plan_id' => $planId,
            'workout' => $result
        ];
    }
    
    return ['success' => false, 'error' => 'Помилка збереження'];
}
?>