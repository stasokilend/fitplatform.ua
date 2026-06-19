<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/google-fit.php';

class GoogleFitController {
    private $pdo;
    private $userId;
    private $accessToken;
    private $refreshToken;
    
    public function __construct($userId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->loadTokens();
    }
    
    /**
     * Загрузка токенов из базы данных
     */
    private function loadTokens() {
        $stmt = $this->pdo->prepare("
            SELECT access_token, refresh_token, token_expires_at 
            FROM user_google_fit_tokens 
            WHERE user_id = ?
        ");
        $stmt->execute([$this->userId]);
        $tokens = $stmt->fetch();
        
        if ($tokens) {
            $this->accessToken = $tokens['access_token'];
            $this->refreshToken = $tokens['refresh_token'];
            
            // Проверяем срок действия токена
            if (strtotime($tokens['token_expires_at']) < time()) {
                $this->refreshAccessToken();
            }
        }
    }
    
    /**
     * Обновление access token
     */
    private function refreshAccessToken() {
        if (!$this->refreshToken) {
            return false;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, GOOGLE_TOKEN_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => GOOGLE_FIT_CLIENT_ID,
            'client_secret' => GOOGLE_FIT_CLIENT_SECRET,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (isset($data['access_token'])) {
            $this->accessToken = $data['access_token'];
            $this->saveTokens($data['access_token'], $this->refreshToken, time() + $data['expires_in']);
            return true;
        }
        
        return false;
    }
    
    /**
     * Сохранение токенов в базу данных
     */
    public function saveTokens($accessToken, $refreshToken, $expiresIn) {
        $expiresAt = date('Y-m-d H:i:s', $expiresIn);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO user_google_fit_tokens (user_id, access_token, refresh_token, token_expires_at)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                access_token = ?, 
                refresh_token = ?, 
                token_expires_at = ?,
                updated_at = NOW()
        ");
        
        return $stmt->execute([
            $this->userId, $accessToken, $refreshToken, $expiresAt,
            $accessToken, $refreshToken, $expiresAt
        ]);
    }
    
    /**
     * Проверка авторизации Google Fit
     */
    public function isConnected() {
        return !empty($this->accessToken) && !empty($this->refreshToken);
    }
    
    /**
     * Получение URL для авторизации Google
     */
    public function getAuthUrl() {
        $params = [
            'client_id' => GOOGLE_FIT_CLIENT_ID,
            'redirect_uri' => GOOGLE_FIT_REDIRECT_URI,
            'response_type' => 'code',
            'scope' => GOOGLE_FIT_SCOPES,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        return GOOGLE_OAUTH_URL . '?' . http_build_query($params);
    }
    
    /**
     * Обмен кода на токены
     */
    public function exchangeCode($code) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, GOOGLE_TOKEN_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => GOOGLE_FIT_CLIENT_ID,
            'client_secret' => GOOGLE_FIT_CLIENT_SECRET,
            'code' => $code,
            'redirect_uri' => GOOGLE_FIT_REDIRECT_URI,
            'grant_type' => 'authorization_code'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (isset($data['access_token']) && isset($data['refresh_token'])) {
            $this->accessToken = $data['access_token'];
            $this->refreshToken = $data['refresh_token'];
            
            $expiresIn = time() + ($data['expires_in'] ?? 3600);
            $this->saveTokens($data['access_token'], $data['refresh_token'], $expiresIn);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Получение данных пользователя Google
     */
    public function getUserInfo() {
        if (!$this->accessToken) {
            return null;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, GOOGLE_USERINFO_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Получение данных об активности за указанный период
     */
    public function getActivityData($startTime, $endTime, $dataType = 'com.google.step_count.delta') {
        if (!$this->accessToken) {
            return null;
        }
        
        $url = GOOGLE_FIT_API_URL . '/dataset:aggregate';
        
        $payload = [
            'aggregateBy' => [
                [
                    'dataTypeName' => $dataType
                ]
            ],
            'bucketByTime' => ['durationMillis' => 86400000], // 1 день
            'startTimeMillis' => strtotime($startTime) * 1000,
            'endTimeMillis' => strtotime($endTime) * 1000
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Получение данных о пульсе
     */
    public function getHeartRateData($startTime, $endTime) {
        return $this->getActivityData($startTime, $endTime, 'com.google.heart_rate.bpm');
    }
    
    /**
     * Получение данных о калориях
     */
    public function getCaloriesData($startTime, $endTime) {
        return $this->getActivityData($startTime, $endTime, 'com.google.calories.expended');
    }
    
    /**
     * Получение данных о весе
     */
    public function getWeightData($startTime, $endTime) {
        if (!$this->accessToken) {
            return null;
        }
        
        $url = GOOGLE_FIT_API_URL . '/dataSources/derived:com.google.weight:com.google.android.gms:merge_weight/datasets/' 
               . strtotime($startTime) . '000000000-' . strtotime($endTime) . '000000000';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Синхронизация данных с Google Fit
     */
    public function syncData($days = 7) {
        if (!$this->isConnected()) {
            return ['success' => false, 'error' => 'Не підключено до Google Fit'];
        }
        
        $endTime = date('Y-m-d H:i:s');
        $startTime = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $results = [];
        
        // Получаем шаги
        $stepsData = $this->getActivityData($startTime, $endTime);
        if ($stepsData) {
            $results['steps'] = $this->parseActivityData($stepsData);
        }
        
        // Получаем калории
        $caloriesData = $this->getCaloriesData($startTime, $endTime);
        if ($caloriesData) {
            $results['calories'] = $this->parseActivityData($caloriesData);
        }
        
        // Получаем пульс
        $heartData = $this->getHeartRateData($startTime, $endTime);
        if ($heartData) {
            $results['heart_rate'] = $this->parseHeartRateData($heartData);
        }
        
        // Сохраняем в базу данных
        $this->saveSyncedData($results, $startTime, $endTime);
        
        return [
            'success' => true,
            'data' => $results,
            'period' => [
                'start' => $startTime,
                'end' => $endTime,
                'days' => $days
            ]
        ];
    }
    
    /**
     * Парсинг данных активности
     */
    private function parseActivityData($data) {
        $result = [];
        
        if (!isset($data['bucket'])) {
            return $result;
        }
        
        foreach ($data['bucket'] as $bucket) {
            $date = date('Y-m-d', round($bucket['startTimeMillis'] / 1000));
            $value = 0;
            
            if (isset($bucket['dataset'][0]['point'][0]['value'][0])) {
                $value = $bucket['dataset'][0]['point'][0]['value'][0]['intVal'] ?? 0;
                if (!$value) {
                    $value = $bucket['dataset'][0]['point'][0]['value'][0]['fpVal'] ?? 0;
                }
            }
            
            $result[$date] = $value;
        }
        
        return $result;
    }
    
    /**
     * Парсинг данных пульса
     */
    private function parseHeartRateData($data) {
        $result = [];
        
        if (!isset($data['bucket'])) {
            return $result;
        }
        
        foreach ($data['bucket'] as $bucket) {
            $date = date('Y-m-d', round($bucket['startTimeMillis'] / 1000));
            $values = [];
            
            if (isset($bucket['dataset'][0]['point'])) {
                foreach ($bucket['dataset'][0]['point'] as $point) {
                    if (isset($point['value'][0]['fpVal'])) {
                        $values[] = $point['value'][0]['fpVal'];
                    }
                }
            }
            
            $result[$date] = [
                'avg' => count($values) > 0 ? round(array_sum($values) / count($values)) : 0,
                'min' => count($values) > 0 ? min($values) : 0,
                'max' => count($values) > 0 ? max($values) : 0,
                'count' => count($values)
            ];
        }
        
        return $result;
    }
    
    /**
     * Сохранение синхронизированных данных
     */
    private function saveSyncedData($data, $startTime, $endTime) {
        // Сохраняем историю синхронизации
        $stmt = $this->pdo->prepare("
            INSERT INTO google_fit_sync_log (user_id, sync_start, sync_end, data_summary)
            VALUES (?, ?, ?, ?)
        ");
        
        $summary = json_encode($data);
        $stmt->execute([$this->userId, $startTime, $endTime, $summary]);
        
        // Сохраняем данные шагов
        if (isset($data['steps'])) {
            foreach ($data['steps'] as $date => $steps) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO user_activity_data (user_id, activity_date, steps, source)
                    VALUES (?, ?, ?, 'google_fit')
                    ON DUPLICATE KEY UPDATE steps = ?, source = 'google_fit'
                ");
                $stmt->execute([$this->userId, $date, $steps, $steps]);
            }
        }
        
        // Сохраняем данные пульса
        if (isset($data['heart_rate'])) {
            foreach ($data['heart_rate'] as $date => $hr) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO heart_rate_logs (user_id, recorded_at, hr_current, hr_rest, source)
                    VALUES (?, ?, ?, ?, 'google_fit')
                ");
                $stmt->execute([$this->userId, $date . ' 00:00:00', $hr['avg'], $hr['min'], 'google_fit']);
            }
        }
    }
    
    /**
     * Отключение от Google Fit
     */
    public function disconnect() {
        $stmt = $this->pdo->prepare("
            DELETE FROM user_google_fit_tokens WHERE user_id = ?
        ");
        return $stmt->execute([$this->userId]);
    }
    
    /**
     * Получение статуса синхронизации
     */
    public function getSyncStatus() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM google_fit_sync_log 
            WHERE user_id = ? 
            ORDER BY sync_start DESC 
            LIMIT 1
        ");
        $stmt->execute([$this->userId]);
        return $stmt->fetch();
    }
}
?>