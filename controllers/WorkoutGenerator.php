<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Класс для генерации персонализированных тренировок
 * Реализует задачу целочисленного программирования (задача о рюкзаке)
 * с множественными целями (вектор целей G)
 */
class WorkoutGenerator {
    private $pdo;
    private $userId;
    private $profile;
    private $exercises;
    private $goalWeights;
    
    public function __construct($userId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->loadProfile();
        $this->loadExercises();
        $this->parseGoalWeights();
    }
    
    /**
     * Загрузка профиля пользователя
     */
    private function loadProfile() {
        $stmt = $this->pdo->prepare("
            SELECT u.*, up.* 
            FROM users u
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$this->userId]);
        $this->profile = $stmt->fetch();
    }
    
    /**
     * Парсинг вектора целей G = ⟨g₁, g₂, g₃⟩
     * g₁ — снижение веса, g₂ — гипертрофия мышц, g₃ — выносливость
     */
    private function parseGoalWeights() {
        $defaultWeights = [
            'weight_loss' => 0.0,
            'muscle_gain' => 0.0,
            'endurance' => 0.0,
            'health' => 0.0
        ];
        
        if (!empty($this->profile['goal_weights'])) {
            $weights = json_decode($this->profile['goal_weights'], true);
            if (is_array($weights)) {
                $this->goalWeights = array_merge($defaultWeights, $weights);
                return;
            }
        }
        
        // Если нет весов, используем основную цель
        $goal = $this->profile['goal_type'] ?? 'health';
        $this->goalWeights = $defaultWeights;
        $this->goalWeights[$goal] = 1.0;
    }
    
    /**
     * Загрузка упражнений с учетом ограничений R
     */
    private function loadExercises() {
        // Получаем ограничения пользователя (R = {r₁, r₂, ..., rₘ})
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
    
    /**
     * Основной метод генерации тренировки
     * Решает задачу целочисленного программирования (задача о рюкзаке)
     * 
     * Максимизация: F(X) = Σᵢ(Σₖ gₖ · dᵢₖ) · xᵢ
     * 
     * Ограничения:
     * - Медицинские (2.7): xᵢ · Σⱼ(rⱼ · mᵢⱼ) = 0
     * - Время (2.8): T_min ≤ (1+γ)·Σᵢ(tᵢ·xᵢ) ≤ T_max
     * - Сложность (2.9): (lᵢ - L)·xᵢ ≤ 0
     * - Энергетическая ёмкость (2.10): Σᵢ(cᵢ·tᵢ·xᵢ) ≥ C_target (если цель - снижение веса)
     */
    public function generateWorkout($duration = 30, $focus = null) {
        $duration = max(10, min(90, (int)$duration));
        $maxDuration = $duration * 1.1; // 10% запас
        
        // Если задан конкретный фокус, корректируем веса
        if ($focus) {
            $tempWeights = $this->goalWeights;
            $this->goalWeights = [
                'weight_loss' => 0,
                'muscle_gain' => 0,
                'endurance' => 0,
                'health' => 0
            ];
            $this->goalWeights[$focus] = 1.0;
        }
        
        // Уровень пользователя L ∈ {1, 2, 3}
        $levelMap = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3];
        $userLevel = $levelMap[$this->profile['fitness_level'] ?? 'beginner'] ?? 1;
        
        // Фильтруем упражнения по уровню сложности (формула 2.9)
        $availableExercises = array_filter($this->exercises, function($ex) use ($userLevel) {
            $exLevel = $levelMap[$ex['difficulty']] ?? 1;
            return $exLevel <= $userLevel + 1;
        });
        
        // Подготовка данных для задачи о рюкзаке
        $items = [];
        foreach ($availableExercises as $ex) {
            // Вычисляем полезность (адаптационный эффект)
            $utility = $this->calculateUtility($ex);
            
            // Вес предмета - время выполнения (с учетом отдыха)
            $weight = $ex['duration_min'] * 1.2; // +20% на отдых
            
            // Проверяем медицинские ограничения (формула 2.7)
            $hasContraindication = $this->hasContraindication($ex);
            if ($hasContraindication) {
                continue;
            }
            
            $items[] = [
                'id' => $ex['id'],
                'name' => $ex['name'],
                'exercise' => $ex,
                'utility' => $utility,
                'weight' => $weight,
                'calories' => $ex['calories_per_min'] * $ex['duration_min']
            ];
        }
        
        // Решаем задачу о рюкзаке (целочисленное программирование)
        $selectedItems = $this->solveKnapsack($items, $maxDuration);
        
        // Проверка энергетической ёмкости (формула 2.10)
        if ($this->goalWeights['weight_loss'] > 0.5) {
            $totalCalories = array_sum(array_column($selectedItems, 'calories'));
            $targetCalories = $duration * 5; // Минимальная цель: 5 ккал/мин
            
            if ($totalCalories < $targetCalories) {
                // Добавляем высокоэнергетические упражнения
                $extraItems = $this->getHighCalorieItems($items, $selectedItems, $maxDuration, $targetCalories - $totalCalories);
                $selectedItems = array_merge($selectedItems, $extraItems);
            }
        }
        
        // Формируем результат
        $exercises = array_column($selectedItems, 'exercise');
        $totalDuration = array_sum(array_column($selectedItems, 'weight'));
        $totalCalories = array_sum(array_column($selectedItems, 'calories'));
        
        // Добавляем sets и reps
        foreach ($exercises as &$ex) {
            $ex['sets'] = $this->calculateSets($ex);
            $ex['reps'] = $this->calculateReps($ex);
        }
        
        return [
            'exercises' => $exercises,
            'total_duration' => round(min($totalDuration, $duration), 1),
            'total_calories' => round($totalCalories),
            'focus' => $focus ?? $this->profile['goal_type'] ?? 'health',
            'count' => count($exercises)
        ];
    }
    
    /**
     * Решение задачи о рюкзаке (целочисленное программирование)
     * Динамическое программирование для максимизации полезности
     */
    private function solveKnapsack($items, $capacity) {
        $n = count($items);
        if ($n === 0 || $capacity <= 0) {
            return [];
        }
        
        // Округляем веса для DP (преобразуем в целые числа)
        $scale = 2; // масштаб для точности
        $capacityInt = (int)($capacity * $scale);
        
        // DP массив: dp[i][w] = максимальная полезность
        $dp = array_fill(0, $n + 1, array_fill(0, $capacityInt + 1, 0));
        $chosen = array_fill(0, $n + 1, array_fill(0, $capacityInt + 1, false));
        
        for ($i = 1; $i <= $n; $i++) {
            $weightInt = (int)($items[$i-1]['weight'] * $scale);
            $utility = $items[$i-1]['utility'];
            
            for ($w = 0; $w <= $capacityInt; $w++) {
                if ($weightInt <= $w) {
                    $valWithItem = $dp[$i-1][$w - $weightInt] + $utility;
                    $valWithoutItem = $dp[$i-1][$w];
                    
                    if ($valWithItem > $valWithoutItem) {
                        $dp[$i][$w] = $valWithItem;
                        $chosen[$i][$w] = true;
                    } else {
                        $dp[$i][$w] = $valWithoutItem;
                    }
                } else {
                    $dp[$i][$w] = $dp[$i-1][$w];
                }
            }
        }
        
        // Восстанавливаем выбранные предметы
        $result = [];
        $w = $capacityInt;
        for ($i = $n; $i > 0; $i--) {
            if ($chosen[$i][$w]) {
                $result[] = $items[$i-1];
                $w -= (int)($items[$i-1]['weight'] * $scale);
            }
        }
        
        return array_reverse($result);
    }
    
    /**
     * Расчет полезности упражнения для пользователя
     * U = Σₖ(gₖ · dᵢₖ)
     */
    private function calculateUtility($exercise) {
        $utility = 0;
        
        // Определяем соответствие упражнения каждой цели (dᵢₖ)
        $goalMapping = $this->getGoalMapping($exercise);
        
        // Вычисляем взвешенную сумму
        $utility = 
            $this->goalWeights['weight_loss'] * $goalMapping['weight_loss'] +
            $this->goalWeights['muscle_gain'] * $goalMapping['muscle_gain'] +
            $this->goalWeights['endurance'] * $goalMapping['endurance'] +
            $this->goalWeights['health'] * $goalMapping['health'];
        
        // Добавляем бонус за сложность (для продвинутых пользователей)
        $levelMap = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3];
        $userLevel = $levelMap[$this->profile['fitness_level'] ?? 'beginner'] ?? 1;
        $exLevel = $levelMap[$exercise['difficulty']] ?? 1;
        
        if ($exLevel <= $userLevel) {
            $utility *= 1.1; // Бонус 10% за соответствие уровню
        }
        
        return $utility;
    }
    
    /**
     * Маппинг упражнения на цели
     * dᵢ = ⟨dᵢ₁, dᵢ₂, dᵢ₃⟩
     */
    private function getGoalMapping($exercise) {
        $muscleGroup = $exercise['muscle_group'];
        $difficulty = $exercise['difficulty'];
        $caloriesPerMin = $exercise['calories_per_min'];
        
        // Базовые значения для разных групп мышц
        $muscleScores = [
            'Ноги' => ['weight_loss' => 0.8, 'muscle_gain' => 0.9, 'endurance' => 0.7, 'health' => 0.6],
            'Груди' => ['weight_loss' => 0.6, 'muscle_gain' => 0.9, 'endurance' => 0.5, 'health' => 0.6],
            'Спина' => ['weight_loss' => 0.7, 'muscle_gain' => 0.8, 'endurance' => 0.6, 'health' => 0.7],
            'Плечі' => ['weight_loss' => 0.5, 'muscle_gain' => 0.7, 'endurance' => 0.5, 'health' => 0.5],
            'Руки' => ['weight_loss' => 0.5, 'muscle_gain' => 0.7, 'endurance' => 0.4, 'health' => 0.5],
            'Прес' => ['weight_loss' => 0.7, 'muscle_gain' => 0.6, 'endurance' => 0.5, 'health' => 0.8],
            'Все тіло' => ['weight_loss' => 0.9, 'muscle_gain' => 0.9, 'endurance' => 0.8, 'health' => 0.9]
        ];
        
        $defaultScores = ['weight_loss' => 0.5, 'muscle_gain' => 0.5, 'endurance' => 0.5, 'health' => 0.5];
        $scores = $muscleScores[$muscleGroup] ?? $defaultScores;
        
        // Корректировка по сложности
        $difficultyMultiplier = [
            'beginner' => 0.8,
            'intermediate' => 1.0,
            'advanced' => 1.2
        ];
        $multiplier = $difficultyMultiplier[$difficulty] ?? 1.0;
        
        // Корректировка по калориям (для weight_loss)
        $calorieBonus = min(1.0, $caloriesPerMin / 8);
        
        return [
            'weight_loss' => min(1.0, $scores['weight_loss'] * $multiplier * (0.8 + 0.2 * $calorieBonus)),
            'muscle_gain' => min(1.0, $scores['muscle_gain'] * $multiplier),
            'endurance' => min(1.0, $scores['endurance'] * $multiplier * (0.9 + 0.1 * ($exercise['duration_min'] / 5))),
            'health' => min(1.0, $scores['health'] * $multiplier)
        ];
    }
    
    /**
     * Проверка медицинских противопоказаний (формула 2.7)
     */
    private function hasContraindication($exercise) {
        static $userRestrictions = null;
        
        if ($userRestrictions === null) {
            $stmt = $this->pdo->prepare("
                SELECT restriction_id FROM user_restrictions WHERE user_id = ?
            ");
            $stmt->execute([$this->userId]);
            $userRestrictions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        if (empty($userRestrictions)) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM exercise_restrictions 
            WHERE exercise_id = ? AND restriction_id IN (" . implode(',', $userRestrictions) . ")
        ");
        $stmt->execute([$exercise['id']]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Получение высококалорийных упражнений для выполнения (2.10)
     */
    private function getHighCalorieItems($items, $selectedItems, $maxDuration, $targetCalories) {
        $selectedIds = array_column($selectedItems, 'id');
        $available = array_filter($items, function($item) use ($selectedIds) {
            return !in_array($item['id'], $selectedIds);
        });
        
        // Сортируем по калориям (убывание)
        usort($available, function($a, $b) {
            return $b['calories'] - $a['calories'];
        });
        
        $result = [];
        $currentDuration = array_sum(array_column($selectedItems, 'weight'));
        $currentCalories = array_sum(array_column($selectedItems, 'calories'));
        
        foreach ($available as $item) {
            if ($currentDuration + $item['weight'] > $maxDuration) {
                continue;
            }
            if ($currentCalories >= $targetCalories) {
                break;
            }
            
            $result[] = $item;
            $currentDuration += $item['weight'];
            $currentCalories += $item['calories'];
        }
        
        return $result;
    }
    
    /**
     * Расчет количества подходов
     */
    private function calculateSets($exercise) {
        $levelMap = ['beginner' => 3, 'intermediate' => 4, 'advanced' => 5];
        $level = $this->profile['fitness_level'] ?? 'beginner';
        $base = $levelMap[$level] ?? 3;
        
        if ($exercise['duration_min'] > 3) {
            return max(2, $base - 1);
        }
        return $base;
    }
    
    /**
     * Расчет количества повторений
     */
    private function calculateReps($exercise) {
        $levelMap = ['beginner' => 10, 'intermediate' => 12, 'advanced' => 15];
        $level = $this->profile['fitness_level'] ?? 'beginner';
        $base = $levelMap[$level] ?? 10;
        
        if ($exercise['difficulty'] === 'advanced') {
            return max(6, $base - 4);
        }
        return $base;
    }
    
    /**
     * Сохранение сгенерированной тренировки
     */
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
                
                $stmt->execute([
                    $planId,
                    $ex['id'],
                    $ex['sets'] ?? $this->calculateSets($ex),
                    $ex['reps'] ?? $this->calculateReps($ex),
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

/**
 * Вспомогательная функция для быстрой генерации
 */
function generateWorkoutForUser($userId, $duration = 30, $focus = null) {
    $generator = new WorkoutGenerator($userId);
    return $generator->generateWorkout($duration, $focus);
}

/**
 * Сохранение тренировки с генерацией
 */
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