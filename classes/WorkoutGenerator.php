<?php
// classes/WorkoutGenerator.php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

class WorkoutGenerator {
    private $db;
    private $user;
    private $profile;
    
    // Конфігурація тренування
    private $minDuration = 20;   // мінімальна тривалість тренування (хв)
    private $maxDuration = 45;   // максимальна тривалість (хв)
    private $restMultiplier = 0.2; // 20% часу на відпочинок
    
    public function __construct(User $user) {
        $this->db = Database::getInstance();
        $this->user = $user;
        $this->profile = $user->getData();
    }
    
    /**
     * Генерація персоналізованого плану тренувань
     * Відповідає математичній моделі з Розділу 2.1
     */
    public function generateWorkout() {
        // 1. Отримання параметрів користувача (вектор U)
        $userParams = $this->getUserParameters();
        
        // 2. Отримання доступних вправ з фільтрацією за обмеженнями
        $availableExercises = $this->getAvailableExercises($userParams);
        
        // 3. Розрахунок корисності для кожної вправи (цільова функція)
        $scoredExercises = $this->scoreExercises($availableExercises, $userParams);
        
        // 4. Оптимізація вибору вправ (задача про ранець)
        $selectedExercises = $this->optimizeSelection($scoredExercises, $userParams);
        
        // 5. Збереження плану в БД
        $planId = $this->saveWorkoutPlan($selectedExercises, $userParams);
        
        // 6. Формування результату
        return [
            'plan_id' => $planId,
            'exercises' => $selectedExercises,
            'total_duration' => $this->calculateTotalDuration($selectedExercises),
            'total_calories' => $this->calculateTotalCalories($selectedExercises),
            'muscle_balance' => $this->calculateMuscleBalance($selectedExercises)
        ];
    }
    
    /**
     * 1. Отримання параметрів користувача (вектор U)
     */
    private function getUserParameters() {
        $profile = $this->profile;
        
        $goals = $profile['goals'] ?? ['lose_weight' => 0.33, 'gain_muscle' => 0.33, 'endurance' => 0.34];
        $restrictions = $profile['medical_restrictions'] ?? [];
        
        return [
            'age' => (int)($profile['age'] ?? 25),
            'weight' => (float)($profile['weight'] ?? 70),
            'height' => (int)($profile['height'] ?? 175),
            'gender' => $profile['gender'] ?? 'male',
            'fitness_level' => (int)($profile['fitness_level'] ?? 1),
            'goals' => $goals,
            'restrictions' => $restrictions,
            // Розрахунок BMR
            'bmr' => $this->user->calculateBMR(
                $profile['weight'] ?? 70,
                $profile['height'] ?? 175,
                $profile['age'] ?? 25,
                $profile['gender'] ?? 'male'
            ),
            // Цільова тривалість тренування (залежить від рівня)
            'target_duration' => $this->getTargetDuration($profile['fitness_level'] ?? 1)
        ];
    }
    
    private function getTargetDuration($level) {
        $durations = [1 => 25, 2 => 35, 3 => 45];
        return $durations[$level] ?? 30;
    }
    
    /**
     * 2. Отримання доступних вправ з фільтрацією за обмеженнями
     * Реалізує обмеження (2.7) та (2.9)
     */
    private function getAvailableExercises($userParams) {
        $sql = "SELECT * FROM exercises";
        $allExercises = $this->db->fetchAll($sql);
        
        $available = [];
        $restrictions = $userParams['restrictions'];
        $level = $userParams['fitness_level'];
        
        foreach ($allExercises as $exercise) {
            // Розпарсити contraindications
            $contraindications = json_decode($exercise['contraindications'] ?? '[]', true);
            
            // Обмеження (2.7): перевірка медичних протипоказань
            $hasContraindication = false;
            foreach ($restrictions as $restriction) {
                if (in_array($restriction, $contraindications)) {
                    $hasContraindication = true;
                    break;
                }
            }
            if ($hasContraindication) continue;
            
            // Обмеження (2.9): перевірка рівня складності
            if ($exercise['difficulty'] > $level) continue;
            
            // Розпарсити muscle_groups
            $exercise['muscle_groups'] = json_decode($exercise['muscle_groups'] ?? '{}', true);
            $exercise['contraindications'] = $contraindications;
            
            $available[] = $exercise;
        }
        
        return $available;
    }
    
    /**
     * 3. Розрахунок корисності для кожної вправи (цільова функція F(X) з формули 2.6)
     */
    private function scoreExercises($exercises, $userParams) {
        $goals = $userParams['goals'];
        $scored = [];
        
        foreach ($exercises as $exercise) {
            // Вектор відповідності вправи цілям (d_i)
            $muscleGroups = $exercise['muscle_groups'];
            $d = [
                'lose_weight' => 0,
                'gain_muscle' => 0,
                'endurance' => 0
            ];
            
            // Для схуднення: висока MET + кардіо
            if (isset($muscleGroups['cardiovascular'])) {
                $d['lose_weight'] = 0.6 * $exercise['met_value'] / 10 + 0.4 * $muscleGroups['cardiovascular'];
            } else {
                $d['lose_weight'] = 0.3 * $exercise['met_value'] / 10;
            }
            
            // Для набору м'язів: опрацювання м'язових груп
            $muscleScore = 0;
            foreach ($muscleGroups as $group => $value) {
                if ($group != 'cardiovascular') {
                    $muscleScore += $value;
                }
            }
            $d['gain_muscle'] = min($muscleScore / 3, 1);
            
            // Для витривалості: тривалість + кардіо
            if (isset($muscleGroups['cardiovascular'])) {
                $d['endurance'] = 0.5 * ($exercise['duration_minutes'] / 5) + 0.5 * $muscleGroups['cardiovascular'];
            } else {
                $d['endurance'] = 0.3 * ($exercise['duration_minutes'] / 5);
            }
            
            // Обмеження: нормалізувати до [0,1]
            foreach ($d as $key => $value) {
                $d[$key] = min(max($value, 0), 1);
            }
            
            // Корисність = сума пріоритетів * відповідність (формула 2.6)
            $utility = $goals['lose_weight'] * $d['lose_weight'] +
                       $goals['gain_muscle'] * $d['gain_muscle'] +
                       $goals['endurance'] * $d['endurance'];
            
            // Додатковий бонус за різноманітність (штраф за однакові групи)
            $exercise['utility'] = $utility;
            $exercise['d_vector'] = $d;
            $scored[] = $exercise;
        }
        
        // Сортування за спаданням корисності
        usort($scored, function($a, $b) {
            return $b['utility'] <=> $a['utility'];
        });
        
        return $scored;
    }
    
    /**
     * 4. Оптимізація вибору вправ (жадібний алгоритм для задачі про ранець)
     * Максимізація корисності при обмеженні на час (2.8)
     */
    private function optimizeSelection($scoredExercises, $userParams) {
        $selected = [];
        $currentDuration = 0;
        $targetDuration = $userParams['target_duration'];
        $maxDuration = min($this->maxDuration, $targetDuration * 1.2);
        $minDuration = max($this->minDuration, $targetDuration * 0.8);
        
        // Жадібний вибір: беремо вправи з найбільшою корисністю,
        // поки не досягнемо мінімальної тривалості
        foreach ($scoredExercises as $exercise) {
            $duration = $exercise['duration_minutes'];
            
            // Перевірка: чи не перевищимо максимальну тривалість
            if ($currentDuration + $duration <= $maxDuration) {
                $selected[] = $exercise;
                $currentDuration += $duration;
            } elseif ($currentDuration < $minDuration && count($selected) > 0) {
                // Якщо ще не досягли мінімуму, спробуємо замінити
                // (для простоти - просто додаємо, навіть якщо перевищимо трохи)
                $selected[] = $exercise;
                $currentDuration += $duration;
                break;
            }
            
            // Якщо досягли мінімуму, можна продовжити, але без перевищення максимуму
            if ($currentDuration >= $minDuration) {
                // Продовжуємо додавати, поки є місце
                continue;
            }
        }
        
        // Якщо обрано менше 3 вправ - додаємо ще з верхівки списку
        if (count($selected) < 3) {
            foreach ($scoredExercises as $exercise) {
                if (!in_array($exercise['id'], array_column($selected, 'id'))) {
                    $duration = $exercise['duration_minutes'];
                    if ($currentDuration + $duration <= $maxDuration * 1.3) {
                        $selected[] = $exercise;
                        $currentDuration += $duration;
                        if (count($selected) >= 5) break;
                    }
                }
            }
        }
        
        // Розрахунок кількості підходів та повторень залежно від цілей
        $goals = $userParams['goals'];
        foreach ($selected as &$exercise) {
            if ($goals['gain_muscle'] > 0.5) {
                // Силовий режим: 3-4 підходи по 8-12 повторень
                $exercise['sets'] = 3;
                $exercise['reps'] = rand(8, 12);
                $exercise['rest_seconds'] = 90;
            } elseif ($goals['lose_weight'] > 0.5) {
                // Жироспалювальний: 3 підходи по 15-20 повторень, менше відпочинку
                $exercise['sets'] = 3;
                $exercise['reps'] = rand(15, 20);
                $exercise['rest_seconds'] = 45;
            } else {
                // Тонус/витривалість
                $exercise['sets'] = 3;
                $exercise['reps'] = rand(12, 15);
                $exercise['rest_seconds'] = 60;
            }
        }
        
        return $selected;
    }
    
    /**
     * 5. Збереження плану в БД
     */
    private function saveWorkoutPlan($exercises, $userParams) {
        $userId = $this->user->getId();
        $totalDuration = $this->calculateTotalDuration($exercises);
        
        // Деактивувати старі плани
        $this->db->query("UPDATE workout_plans SET is_active = FALSE WHERE user_id = ? AND is_active = TRUE", [$userId]);
        
        // Створити новий план
        $planId = $this->db->insert('workout_plans', [
            'user_id' => $userId,
            'name' => 'Тренування ' . date('d.m.Y H:i'),
            'total_duration' => $totalDuration,
            'is_active' => 1
        ]);
        
        // Додати вправи до плану
        $orderIndex = 0;
        foreach ($exercises as $exercise) {
            $this->db->insert('plan_exercises', [
                'plan_id' => $planId,
                'exercise_id' => $exercise['id'],
                'sets' => $exercise['sets'],
                'reps' => $exercise['reps'],
                'rest_seconds' => $exercise['rest_seconds'],
                'order_index' => $orderIndex++
            ]);
        }
        
        return $planId;
    }
    
    /**
     * Допоміжні методи
     */
    private function calculateTotalDuration($exercises) {
        $duration = 0;
        $restMultiplier = $this->restMultiplier;
        
        foreach ($exercises as $exercise) {
            // Час на виконання + відпочинок
            $duration += $exercise['duration_minutes'] + ($exercise['duration_minutes'] * $restMultiplier);
        }
        
        return (int)round($duration);
    }
    
    private function calculateTotalCalories($exercises) {
        $calories = 0;
        foreach ($exercises as $exercise) {
            // MET * вага * час (в годинах)
            $calories += $exercise['met_value'] * 3.5 * 70 / 200 * ($exercise['duration_minutes'] / 60);
        }
        return round($calories, 1);
    }
    
    private function calculateMuscleBalance($exercises) {
        $balance = [
            'legs' => 0,
            'core' => 0,
            'arms' => 0,
            'chest' => 0,
            'back' => 0,
            'shoulders' => 0,
            'cardiovascular' => 0
        ];
        
        foreach ($exercises as $exercise) {
            $groups = $exercise['muscle_groups'] ?? [];
            foreach ($groups as $group => $value) {
                if (isset($balance[$group])) {
                    $balance[$group] += $value;
                }
            }
        }
        
        // Нормалізація
        $max = max($balance);
        if ($max > 0) {
            foreach ($balance as $key => $value) {
                $balance[$key] = round($value / $max, 2);
            }
        }
        
        return $balance;
    }
}
?>