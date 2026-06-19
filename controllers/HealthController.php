<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Контроллер для мониторинга здоровья
 * Реализует метод Карвонена (формулы 2.11 - 2.14)
 */
class HealthController {
    private $pdo;
    private $userId;
    private $profile;
    
    public function __construct($userId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->loadProfile();
    }
    
    /**
     * Загрузка профиля пользователя
     */
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
    
    /**
     * Расчет максимальной ЧСС по формуле Танака (2.11)
     * HRmax = 208 - 0.7 * возраст
     */
    public function calculateMaxHeartRate() {
        $age = $this->profile['age'] ?? 30;
        return round(208 - 0.7 * $age);
    }
    
    /**
     * Расчет резерва сердца (2.12)
     * HRreserve = HRmax - HRrest
     */
    public function calculateHeartRateReserve($hrRest = null) {
        if ($hrRest === null) {
            $hrRest = $this->getRestingHeartRate();
        }
        $hrMax = $this->calculateMaxHeartRate();
        return $hrMax - $hrRest;
    }
    
    /**
     * Расчет целевых зон пульса по методу Карвонена (2.13 - 2.14)
     * THR = HRrest + k * HRreserve
     */
    public function calculateTargetZones($hrRest = null) {
        if ($hrRest === null) {
            $hrRest = $this->getRestingHeartRate();
        }
        
        $hrMax = $this->calculateMaxHeartRate();
        $hrReserve = $hrMax - $hrRest;
        
        // Коэффициенты интенсивности для разных зон
        $zones = [
            'warmup' => [
                'name' => 'Розминка',
                'icon' => 'bi-thermometer-low',
                'color' => '#6C63FF',
                'min' => 0.5,
                'max' => 0.6,
                'description' => 'Легка активність, підготовка організму'
            ],
            'fat_burn' => [
                'name' => 'Жироспалювання',
                'icon' => 'bi-fire',
                'color' => '#FFB347',
                'min' => 0.6,
                'max' => 0.7,
                'description' => 'Оптимальна зона для спалювання жиру'
            ],
            'aerobic' => [
                'name' => 'Аеробна',
                'icon' => 'bi-heart-pulse',
                'color' => '#00D2A0',
                'min' => 0.7,
                'max' => 0.8,
                'description' => 'Покращення серцево-судинної системи'
            ],
            'anaerobic' => [
                'name' => 'Анаеробна',
                'icon' => 'bi-lightning',
                'color' => '#FF6584',
                'min' => 0.8,
                'max' => 0.9,
                'description' => 'Висока інтенсивність, розвиток сили'
            ],
            'max' => [
                'name' => 'Максимальна',
                'icon' => 'bi-activity',
                'color' => '#FF6B6B',
                'min' => 0.9,
                'max' => 1.0,
                'description' => 'Граничне навантаження, лише для підготовлених'
            ]
        ];
        
        $result = [];
        foreach ($zones as $key => $zone) {
            $result[$key] = [
                'name' => $zone['name'],
                'icon' => $zone['icon'],
                'color' => $zone['color'],
                'description' => $zone['description'],
                'min_hr' => round($hrRest + $zone['min'] * $hrReserve),
                'max_hr' => round($hrRest + $zone['max'] * $hrReserve),
                'min_percent' => round($zone['min'] * 100),
                'max_percent' => round($zone['max'] * 100)
            ];
        }
        
        return $result;
    }
    
    /**
     * Получение пульса в покое из последних записей
     */
    public function getRestingHeartRate() {
        $stmt = $this->pdo->prepare("
            SELECT hr_rest FROM heart_rate_logs 
            WHERE user_id = ? 
            ORDER BY recorded_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$this->userId]);
        $result = $stmt->fetch();
        
        if ($result && $result['hr_rest'] > 0) {
            return (int)$result['hr_rest'];
        }
        
        // Значение по умолчанию
        return 70;
    }
    
    /**
     * Сохранение показателей пульса
     */
    public function saveHeartRate($hrRest, $hrCurrent = null, $hrMax = null) {
        if ($hrMax === null) {
            $hrMax = $this->calculateMaxHeartRate();
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO heart_rate_logs (user_id, hr_rest, hr_current, hr_max)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$this->userId, $hrRest, $hrCurrent, $hrMax]);
    }
    
    /**
     * Получение истории пульса
     */
    public function getHeartRateHistory($days = 30) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM heart_rate_logs 
            WHERE user_id = ? AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY recorded_at DESC
        ");
        $stmt->execute([$this->userId, $days]);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение статистики здоровья
     */
    public function getHealthStats() {
        $stats = [];
        
        // Последний пульс
        $stmt = $this->pdo->prepare("
            SELECT hr_rest, hr_current, recorded_at 
            FROM heart_rate_logs 
            WHERE user_id = ? 
            ORDER BY recorded_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$this->userId]);
        $stats['last_heart_rate'] = $stmt->fetch();
        
        // Средний пульс за неделю
        $stmt = $this->pdo->prepare("
            SELECT 
                AVG(hr_rest) as avg_rest,
                AVG(hr_current) as avg_current,
                MIN(hr_rest) as min_rest,
                MAX(hr_rest) as max_rest
            FROM heart_rate_logs 
            WHERE user_id = ? AND recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute([$this->userId]);
        $stats['weekly'] = $stmt->fetch();
        
        // Количество записей
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM heart_rate_logs WHERE user_id = ?
        ");
        $stmt->execute([$this->userId]);
        $stats['total_records'] = $stmt->fetch()['total'] ?? 0;
        
        // Целевые зоны
        $stats['target_zones'] = $this->calculateTargetZones();
        $stats['max_hr'] = $this->calculateMaxHeartRate();
        $stats['resting_hr'] = $this->getRestingHeartRate();
        
        return $stats;
    }
    
    /**
     * Определение текущей зоны пульса
     */
    public function getCurrentZone($hrCurrent) {
        $zones = $this->calculateTargetZones();
        
        foreach ($zones as $key => $zone) {
            if ($hrCurrent >= $zone['min_hr'] && $hrCurrent <= $zone['max_hr']) {
                return [
                    'zone' => $key,
                    'name' => $zone['name'],
                    'icon' => $zone['icon'],
                    'color' => $zone['color'],
                    'percent' => round(($hrCurrent - $zone['min_hr']) / ($zone['max_hr'] - $zone['min_hr']) * 100)
                ];
            }
        }
        
        // Если пульс ниже минимальной зоны
        if ($hrCurrent < $zones['warmup']['min_hr']) {
            return [
                'zone' => 'below',
                'name' => 'Нижче норми',
                'icon' => 'bi-arrow-down',
                'color' => '#6C757D',
                'percent' => 0
            ];
        }
        
        // Если пульс выше максимальной зоны
        return [
            'zone' => 'above',
            'name' => 'Перевищення',
            'icon' => 'bi-exclamation-triangle',
            'color' => '#DC3545',
            'percent' => 100
        ];
    }
}
?>