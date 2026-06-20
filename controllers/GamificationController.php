<?php
require_once __DIR__ . '/../config/database.php';

class GamificationController {
    private $pdo;
    private $userId;
    private $stats;
    
    public function __construct($userId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->loadStats();
    }
    
    /**
     * Загрузка статистики пользователя
     */
    private function loadStats() {
        // Проверяем наличие записи
        $stmt = $this->pdo->prepare("SELECT * FROM user_gamification_stats WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $this->stats = $stmt->fetch();
        
        if (!$this->stats) {
            // Создаем запись
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
     * Обновление статистики после тренировки
     */
    public function updateStats($calories, $exercisesCompleted, $isComplete = true) {
        // Обновляем основные показатели
        $stmt = $this->pdo->prepare("
            UPDATE user_gamification_stats 
            SET 
                total_workouts = total_workouts + 1,
                total_calories = total_calories + ?,
                total_exercises_completed = total_exercises_completed + ?,
                last_workout_date = CURDATE()
            WHERE user_id = ?
        ");
        $stmt->execute([$calories, $exercisesCompleted, $this->userId]);
        
        // Обновляем серию
        $this->updateStreak();
        
        // Обновляем опыт и уровень
        $this->addExperience($exercisesCompleted * 5 + $calories / 10);
        
        // Проверяем достижения
        $this->checkAchievements();
        
        // Обновляем локальную статистику
        $this->loadStats();
    }
    
    /**
     * Обновление серии (стрейка)
     */
    private function updateStreak() {
        $lastDate = $this->stats['last_workout_date'] ?? null;
        $today = date('Y-m-d');
        
        if ($lastDate === $today) {
            // Уже тренировался сегодня
            return;
        }
        
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        if ($lastDate === $yesterday) {
            // Продолжаем серию
            $newStreak = $this->stats['current_streak'] + 1;
        } else {
            // Начинаем новую серию
            $newStreak = 1;
        }
        
        // Обновляем максимальную серию
        $maxStreak = max($newStreak, $this->stats['max_streak']);
        
        $stmt = $this->pdo->prepare("
            UPDATE user_gamification_stats 
            SET current_streak = ?, max_streak = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$newStreak, $maxStreak, $this->userId]);
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
        
        // Проверяем повышение уровня
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
        
        // Проверяем достижения по уровню
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
     * Проверка всех достижений
     */
    public function checkAchievements() {
        // Получаем все активные достижения
        $stmt = $this->pdo->query("
            SELECT * FROM achievements WHERE is_active = 1
        ");
        $achievements = $stmt->fetchAll();
        
        foreach ($achievements as $achievement) {
            $this->checkAchievement($achievement);
        }
    }
    
    /**
     * Проверка конкретного достижения
     */
    private function checkAchievement($achievement) {
        $current = $this->getCurrentProgress($achievement);
        
        if ($current >= $achievement['requirement_value']) {
            $this->unlockAchievement($achievement['code']);
        } else {
            // Обновляем прогресс
            $this->updateProgress($achievement['code'], $current);
        }
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
     * Получение текущего прогресса для достижения
     */
    private function getCurrentProgress($achievement) {
        $type = $achievement['requirement_type'];
        
        switch ($type) {
            case 'workouts':
                return $this->stats['total_workouts'] ?? 0;
            case 'streak':
                return $this->stats['current_streak'] ?? 0;
            case 'calories':
                return $this->stats['total_calories'] ?? 0;
            case 'exercises':
                return $this->stats['total_exercises_completed'] ?? 0;
            case 'special':
                return 0;
            default:
                return 0;
        }
    }
    
    /**
     * Обновление прогресса достижения
     */
    private function updateProgress($achievementCode, $progress) {
        $stmt = $this->pdo->prepare("
            UPDATE user_achievements 
            SET progress = ? 
            WHERE user_id = ? AND achievement_id = (
                SELECT id FROM achievements WHERE code = ?
            )
        ");
        $stmt->execute([$progress, $this->userId, $achievementCode]);
    }
    
    /**
     * Разблокировка достижения
     */
    public function unlockAchievement($achievementCode) {
        // Проверяем, не разблокировано ли уже
        $stmt = $this->pdo->prepare("
            SELECT id FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id
            WHERE ua.user_id = ? AND a.code = ? AND ua.is_completed = 1
        ");
        $stmt->execute([$this->userId, $achievementCode]);
        if ($stmt->fetch()) {
            return false;
        }
        
        // Получаем ID достижения
        $stmt = $this->pdo->prepare("SELECT id, points FROM achievements WHERE code = ?");
        $stmt->execute([$achievementCode]);
        $achievement = $stmt->fetch();
        
        if (!$achievement) {
            return false;
        }
        
        // Добавляем или обновляем запись
        $stmt = $this->pdo->prepare("
            INSERT INTO user_achievements (user_id, achievement_id, is_completed, progress)
            VALUES (?, ?, 1, ?)
            ON DUPLICATE KEY UPDATE is_completed = 1, progress = ?, unlocked_at = NOW()
        ");
        $stmt->execute([$this->userId, $achievement['id'], $achievement['points'], $achievement['points']]);
        
        // Добавляем опыт за достижение
        $this->addExperience($achievement['points'] * 2);
        
        return true;
    }
    
    /**
     * Получение всех достижений пользователя
     */
    public function getUserAchievements() {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.*,
                ua.is_completed,
                ua.unlocked_at,
                ua.progress,
                CASE WHEN ua.is_completed = 1 THEN ua.progress ELSE 0 END as points_earned
            FROM achievements a
            LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
            ORDER BY ua.is_completed DESC, a.category, a.requirement_value ASC
        ");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
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
            'max_streak' => $this->stats['max_streak'] ?? 0,
            'total_workouts' => $this->stats['total_workouts'] ?? 0,
            'total_calories' => $this->stats['total_calories'] ?? 0
        ];
    }
    
/**
 * Получение последних разблокированных достижений с деталями
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
 * Получение статистики достижений для дашборда
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
?>