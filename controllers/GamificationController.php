<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/NotificationController.php';

class GamificationController {
    private $pdo;
    private $userId;
    private $stats;
    
    public function __construct($userId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->loadStats();
        $this->ensureFirstWorkoutAchievementExists();
    }
    
    /**
     * Загрузка статистики пользователя
     */
    private function loadStats() {
        $stmt = $this->pdo->prepare("SELECT * FROM user_gamification_stats WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $this->stats = $stmt->fetch();
        
        if (!$this->stats) {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_gamification_stats (user_id, total_workouts, total_calories)
                VALUES (?, 0, 0)
            ");
            $stmt->execute([$this->userId]);
            
            $stmt = $this->pdo->prepare("SELECT * FROM user_gamification_stats WHERE user_id = ?");
            $stmt->execute([$this->userId]);
            $this->stats = $stmt->fetch();
        }
    }
    
    /**
     * Гарантируем существование достижения "Первая тренировка"
     */
    private function ensureFirstWorkoutAchievementExists() {
        $stmt = $this->pdo->prepare("SELECT id FROM achievements WHERE code = 'first_workout'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare("
                INSERT INTO achievements 
                (code, name, description, category, rarity, requirement_type, requirement_value, points, icon, is_active) 
                VALUES 
                ('first_workout', 'Перша тренування', 'Виконайте своє перше тренування', 'workout', 'common', 'workouts', 1, 10, 'bi-trophy', 1)
            ");
            $stmt->execute();
        }
    }
    
    /**
     * Обновление статистики после тренировки
     */
    public function updateStats($calories, $exercisesCompleted, $isComplete = true) {
        $calories = max(0, (int)round($calories));
        $exercisesCompleted = max(0, (int)$exercisesCompleted);

        if ($isComplete) {
            $this->updateStreak();
        }

        $stmt = $this->pdo->prepare("
            UPDATE user_gamification_stats 
            SET 
                total_workouts = total_workouts + ?,
                total_calories = total_calories + ?,
                total_exercises_completed = total_exercises_completed + ?,
                last_workout_date = CURDATE()
            WHERE user_id = ?
        ");
        $stmt->execute([$isComplete ? 1 : 0, $calories, $exercisesCompleted, $this->userId]);

        $this->loadStats();
        
        $this->addExperience($exercisesCompleted * 5 + $calories / 10);
        $this->checkStatMilestoneAchievements();
        $this->loadStats();
    }
    
    /**
     * Обработка события завершения тренировки
     */
    public function recordWorkoutCompleted($workoutId, $calories, $exercisesCompleted, $totalExercises = null) {
        // Сохраняем старую статистику для проверки первой тренировки
        $oldTotalWorkouts = (int)($this->stats['total_workouts'] ?? 0);
        
        // Обновляем статистику с калориями
        $this->updateStats($calories, $exercisesCompleted, true);
        
        // Проверяем специальные достижения
        $this->checkWorkoutEventAchievements(
            (int)$workoutId,
            max(0, (int)$exercisesCompleted),
            $totalExercises === null ? null : max(0, (int)$totalExercises)
        );
        
        // Полная синхронизация всех достижений
        $this->syncAchievements();
        $this->loadStats();
        
        // Явная проверка: если это была первая тренировка (до обновления было 0, после стало 1)
        if ($oldTotalWorkouts === 0 && ($this->stats['total_workouts'] ?? 0) >= 1) {
            $this->unlockAchievement('first_workout');
        }
    }

    private function ensureGamificationStats() {
        $stmt = $this->pdo->prepare("SELECT user_id FROM user_gamification_stats WHERE user_id = ?");
        $stmt->execute([$this->userId]);

        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_gamification_stats
                (user_id, total_workouts, total_calories, current_streak, max_streak, total_exercises_completed)
                VALUES (?, 0, 0, 0, 0, 0)
            ");
            $stmt->execute([$this->userId]);
        }
    }

    /**
     * Ручная синхронизация ВСЕХ достижений пользователя
     */
    public function syncAchievements() {
        $this->loadStats();
        
        // 1. Синхронизируем обычные достижения (workouts, streak, calories, exercises)
        $this->checkAchievementsByType('workouts', (int)($this->stats['total_workouts'] ?? 0));
        $this->checkAchievementsByType('streak', (int)($this->stats['current_streak'] ?? 0));
        $this->checkAchievementsByType('calories', (int)($this->stats['total_calories'] ?? 0));
        $this->checkAchievementsByType('exercises', (int)($this->stats['total_exercises_completed'] ?? 0));
        
        // 2. Синхронизируем достижения по уровню
        $this->checkLevelAchievements($this->getCurrentLevelNumber());
        
        // 3. Синхронизируем СПЕЦИАЛЬНЫЕ достижения
        $this->syncSpecialAchievements();
        
        $this->loadStats();
        
        return $this->getGamificationStats();
    }

    /**
     * Синхронизация специальных достижений
     */
    private function syncSpecialAchievements() {
        // first_workout - первая тренировка (уже обработана в recordWorkoutCompleted)
        // но на всякий случай проверим ещё раз
        if (($this->stats['total_workouts'] ?? 0) >= 1) {
            $this->unlockAchievement('first_workout');
        }
        
        // perfect_workout - идеальная тренировка (все упражнения выполнены)
        $fullWorkouts = $this->getFullyCompletedWorkoutsCount();
        if ($fullWorkouts >= 1) {
            $this->unlockAchievement('perfect_workout');
        }
        
        // consistency - 10 идеальных тренировок
        if ($fullWorkouts >= 10) {
            $this->unlockAchievement('consistency');
        } else {
            $this->updateProgress('consistency', $fullWorkouts);
        }
        
        // early_bird и night_owl не могут быть проверены постфактум без логов времени
        // Они активируются только во время реальной тренировки
    }

    /**
     * Проверка накопительных достижений
     */
    private function checkStatMilestoneAchievements() {
        $this->checkAchievementsByType('workouts', (int)($this->stats['total_workouts'] ?? 0));
        $this->checkAchievementsByType('streak', (int)($this->stats['current_streak'] ?? 0));
        $this->checkAchievementsByType('calories', (int)($this->stats['total_calories'] ?? 0));
        $this->checkAchievementsByType('exercises', (int)($this->stats['total_exercises_completed'] ?? 0));
        $this->checkLevelAchievements($this->getCurrentLevelNumber());
    }

    /**
     * Проверка всех достижений одного типа
     */
    private function checkAchievementsByType($requirementType, $currentProgress) {
        $stmt = $this->pdo->prepare("
            SELECT id, code, requirement_value
            FROM achievements
            WHERE is_active = 1 AND requirement_type = ?
            ORDER BY requirement_value ASC
        ");
        $stmt->execute([$requirementType]);

        foreach ($stmt->fetchAll() as $achievement) {
            if ($currentProgress >= (int)$achievement['requirement_value']) {
                $this->unlockAchievement($achievement['code']);
            } else {
                $this->updateProgress($achievement['code'], $currentProgress);
            }
        }
    }

    /**
     * Проверка достижений, зависящих от события тренировки
     */
    private function checkWorkoutEventAchievements($workoutId, $exercisesCompleted, $totalExercises = null) {
        $hour = (int)date('G');

        if ($hour < 7) {
            $this->unlockAchievement('early_bird');
        }

        if ($hour >= 22) {
            $this->unlockAchievement('night_owl');
        }

        if ($totalExercises === null) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM plan_exercises
                WHERE plan_id = ?
            ");
            $stmt->execute([$workoutId]);
            $totalExercises = (int)($stmt->fetch()['total'] ?? 0);
        }

        if ($totalExercises > 0 && $exercisesCompleted >= $totalExercises) {
            $this->unlockAchievement('perfect_workout');
            $this->updateProgress('perfect_workout', $totalExercises);
        } else {
            $this->updateProgress('perfect_workout', $exercisesCompleted);
        }

        $fullWorkouts = $this->getFullyCompletedWorkoutsCount();
        if ($fullWorkouts >= 10) {
            $this->unlockAchievement('consistency');
        } else {
            $this->updateProgress('consistency', $fullWorkouts);
        }
    }

    /**
     * Количество идеальных тренировок пользователя
     */
    private function getFullyCompletedWorkoutsCount() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total
            FROM (
                SELECT wp.id
                FROM workout_plans wp
                JOIN plan_exercises pe ON pe.plan_id = wp.id
                WHERE wp.user_id = ? AND wp.status = 'completed'
                GROUP BY wp.id
                HAVING COUNT(pe.id) > 0
                   AND COUNT(pe.id) = SUM(CASE WHEN pe.is_completed = 1 THEN 1 ELSE 0 END)
            ) completed_plans
        ");
        $stmt->execute([$this->userId]);

        return (int)($stmt->fetch()['total'] ?? 0);
    }

    /**
     * Обновление серии (стрейка)
     */
    private function updateStreak() {
        $lastDate = $this->stats['last_workout_date'] ?? null;
        $today = date('Y-m-d');
        
        if ($lastDate === $today) {
            return;
        }
        
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        if ($lastDate === $yesterday) {
            $newStreak = $this->stats['current_streak'] + 1;
        } else {
            $newStreak = 1;
        }
        
        $maxStreak = max($newStreak, $this->stats['longest_streak']);
        
        $stmt = $this->pdo->prepare("
            UPDATE user_gamification_stats 
            SET current_streak = ?, longest_streak = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$newStreak, $maxStreak, $this->userId]);
        $this->stats['current_streak'] = $newStreak;
        $this->stats['longest_streak'] = $maxStreak;
    }
    
    /**
     * Добавление опыта
     */
    public function addExperience($points) {
        $stmt = $this->pdo->prepare("SELECT * FROM user_levels WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $level = $stmt->fetch();
        
        if (!$level) {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_levels (user_id, level, experience, next_level_experience, total_experience)
                VALUES (?, 1, 0, 100, 0)
            ");
            $stmt->execute([$this->userId]);
            
            $stmt = $this->pdo->prepare("SELECT * FROM user_levels WHERE user_id = ?");
            $stmt->execute([$this->userId]);
            $level = $stmt->fetch();
        }
        
        $newExp = $level['experience'] + $points;
        $newTotalExp = $level['total_experience'] + $points;
        $newLevel = $level['level'];
        $nextLevelExp = $level['next_level_experience'];
        
        while ($newExp >= $nextLevelExp) {
            $newExp -= $nextLevelExp;
            $newLevel++;
            $nextLevelExp = floor($nextLevelExp * 1.3);
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE user_levels 
            SET 
                level = ?,
                experience = ?,
                next_level_experience = ?,
                total_experience = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$newLevel, $newExp, $nextLevelExp, $newTotalExp, $this->userId]);
        
        $this->checkLevelAchievements($newLevel);
    }
    
    /**
     * Проверка достижений по уровню
     */
    private function checkLevelAchievements($level) {
        $levelAchievements = [
            5 => 'level_5',
            10 => 'level_10',
            25 => 'level_25',
            50 => 'level_50',
            100 => 'level_100'
        ];
        
        foreach ($levelAchievements as $requiredLevel => $code) {
            if ($level >= $requiredLevel) {
                $this->unlockAchievement($code);
            }
        }
    }

    /**
     * Текущий уровень пользователя
     */
    private function getCurrentLevelNumber() {
        $stmt = $this->pdo->prepare("SELECT level FROM user_levels WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $level = $stmt->fetch();

        return $level ? (int)$level['level'] : 1;
    }
    
    /**
     * Обновление прогресса достижения
     */
    private function updateProgress($achievementCode, $progress) {
        $stmt = $this->pdo->prepare("SELECT id FROM achievements WHERE code = ?");
        $stmt->execute([$achievementCode]);
        $achievement = $stmt->fetch();

        if (!$achievement) {
            return;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO user_achievements (user_id, achievement_id, is_completed, progress)
            VALUES (?, ?, 0, ?)
            ON DUPLICATE KEY UPDATE progress = IF(is_completed = 1, progress, VALUES(progress))
        ");
        $stmt->execute([$this->userId, $achievement['id'], max(0, (int)round($progress))]);
    }
    
    /**
     * Разблокировка достижения
     */
    public function unlockAchievement($achievementCode) {
        // Уже открыто
        $stmt = $this->pdo->prepare("
            SELECT ua.id
            FROM user_achievements ua
            JOIN achievements a ON a.id = ua.achievement_id
            WHERE ua.user_id = ? AND a.code = ? AND ua.is_completed = 1
        ");
        $stmt->execute([$this->userId, $achievementCode]);

        if ($stmt->fetch()) {
            return false;
        }

        // Получаем достижение
        $stmt = $this->pdo->prepare("SELECT * FROM achievements WHERE code = ? AND is_active = 1");
        $stmt->execute([$achievementCode]);
        $achievement = $stmt->fetch();

        if (!$achievement) {
            return false;
        }

        // Если запись уже существует
        $stmt = $this->pdo->prepare("SELECT id FROM user_achievements WHERE user_id = ? AND achievement_id = ?");
        $stmt->execute([$this->userId, $achievement['id']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $this->pdo->prepare("
                UPDATE user_achievements
                SET is_completed = 1, progress = ?, unlocked_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$achievement['requirement_value'], $existing['id']]);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_achievements (user_id, achievement_id, progress, is_completed, unlocked_at)
                VALUES (?, ?, ?, 1, NOW())
            ");
            $stmt->execute([$this->userId, $achievement['id'], $achievement['requirement_value']]);
        }

        $this->notifyAchievementUnlocked($achievement);
        return true;
    }
    
    /**
     * Уведомление о новом достижении
     */
    private function notifyAchievementUnlocked($achievement) {
        $notification = new NotificationController($this->userId);
        $created = $notification->createFromTemplate('achievement_unlocked', [
            'achievement_name' => $achievement['name'],
            'points' => $achievement['points'],
            'link' => '/dashboard.php?page=achievements'
        ]);

        if (!$created) {
            $notification->create(
                'achievement',
                'Нове досягнення! 🏆',
                'Ви отримали досягнення «' . $achievement['name'] . '» та +' . (int)$achievement['points'] . ' балів.',
                $achievement['icon'] ?: 'bi-trophy',
                '/dashboard.php?page=achievements'
            );
        }
    }

    /**
     * Получение всех достижений пользователя
     */
    public function getUserAchievements() {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.*,
                COALESCE(ua.is_completed, 0) as is_completed,
                ua.unlocked_at,
                COALESCE(ua.progress, 0) as progress,
                CASE WHEN ua.is_completed = 1 THEN ua.progress ELSE 0 END as points_earned
            FROM achievements a
            LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
            WHERE a.is_active = 1
            ORDER BY ua.is_completed DESC, a.category, a.order_num ASC, a.requirement_value ASC
        ");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение достижений по категориям
     */
    public function getAchievementsByCategory() {
        $achievements = $this->getUserAchievements();
        $categories = [];
        
        foreach ($achievements as $ach) {
            $cat = $ach['category'] ?? 'other';
            if (!isset($categories[$cat])) {
                $categories[$cat] = [];
            }
            $categories[$cat][] = $ach;
        }
        
        return $categories;
    }
    
    /**
     * Получение информации об уровне пользователя
     */
    public function getUserLevel() {
        $stmt = $this->pdo->prepare("SELECT * FROM user_levels WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $level = $stmt->fetch();
        
        if (!$level) {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_levels (user_id, level, experience, next_level_experience)
                VALUES (?, 1, 0, 100)
            ");
            $stmt->execute([$this->userId]);
            
            $stmt = $this->pdo->prepare("SELECT * FROM user_levels WHERE user_id = ?");
            $stmt->execute([$this->userId]);
            $level = $stmt->fetch();
        }
        
        return $level;
    }
    
    /**
     * Получение статистики геймификации
     */
    public function getGamificationStats() {
        $level = $this->getUserLevel();
        $achievements = $this->getUserAchievements();
        
        $total = count($achievements);
        $completed = 0;
        $totalPoints = 0;
        
        foreach ($achievements as $ach) {
            if ($ach['is_completed']) {
                $completed++;
                $totalPoints += $ach['points'];
            }
        }
        
        return [
            'level' => $level['level'],
            'experience' => $level['experience'],
            'next_level' => $level['next_level_experience'],
            'total_experience' => $level['total_experience'],
            'total_achievements' => $total,
            'completed_achievements' => $completed,
            'total_points' => $totalPoints,
            'completion_percent' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'streak' => $this->stats['current_streak'] ?? 0,
            'longest_streak' => $this->stats['longest_streak'] ?? 0,
            'total_workouts' => $this->stats['total_workouts'] ?? 0,
            'total_calories' => $this->stats['total_calories'] ?? 0
        ];
    }
    
    /**
     * Получение последних разблокированных достижений
     */
    public function getRecentAchievements($limit = 5) {
        $limit = (int)$limit;
        
        $stmt = $this->pdo->prepare("
            SELECT 
                a.*,
                ua.unlocked_at,
                ua.progress,
                ua.is_completed
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id
            WHERE ua.user_id = ? AND ua.is_completed = 1
            ORDER BY ua.unlocked_at DESC
            LIMIT " . $limit . "
        ");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение сводной статистики достижений
     */
    public function getAchievementSummary() {
        $achievements = $this->getUserAchievements();
        
        $total = count($achievements);
        $completed = 0;
        $byRarity = [
            'common' => ['total' => 0, 'completed' => 0],
            'uncommon' => ['total' => 0, 'completed' => 0],
            'rare' => ['total' => 0, 'completed' => 0],
            'epic' => ['total' => 0, 'completed' => 0],
            'legendary' => ['total' => 0, 'completed' => 0]
        ];
        
        foreach ($achievements as $ach) {
            $rarity = $ach['rarity'] ?? 'common';
            if (isset($byRarity[$rarity])) {
                $byRarity[$rarity]['total']++;
                if ($ach['is_completed']) {
                    $byRarity[$rarity]['completed']++;
                    $completed++;
                }
            }
        }
        
        return [
            'total' => $total,
            'completed' => $completed,
            'by_rarity' => $byRarity,
            'completion_percent' => $total > 0 ? round(($completed / $total) * 100) : 0
        ];
    }
}