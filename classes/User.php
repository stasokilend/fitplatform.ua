<?php
// classes/User.php

require_once __DIR__ . '/Database.php';

class User {
    private $db;
    private $id;
    private $data = [];
    
    public function __construct($userId = null) {
        $this->db = Database::getInstance();
        if ($userId) {
            $this->id = $userId;
            $this->load();
        }
    }
    
    public function load() {
        $sql = "SELECT u.*, p.* FROM users u 
                LEFT JOIN user_profiles p ON u.id = p.user_id 
                WHERE u.id = ?";
        $result = $this->db->fetchOne($sql, [$this->id]);
        if ($result) {
            $this->data = $result;
            // Розпарсити JSON-поля
            if (isset($this->data['goals']) && is_string($this->data['goals'])) {
                $this->data['goals'] = json_decode($this->data['goals'], true);
            }
            if (isset($this->data['medical_restrictions']) && is_string($this->data['medical_restrictions'])) {
                $this->data['medical_restrictions'] = json_decode($this->data['medical_restrictions'], true);
            }
        }
        return $this->data;
    }
    
    public function register($email, $password, $fullName) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password_hash, full_name) VALUES (?, ?, ?)";
        try {
            $this->db->query($sql, [$email, $passwordHash, $fullName]);
            $this->id = $this->db->getConnection()->lastInsertId();
            $this->load();
            return true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                return false; // Email вже існує
            }
            throw $e;
        }
    }
    
    public function login($email, $password) {
        $sql = "SELECT id, password_hash FROM users WHERE email = ?";
        $result = $this->db->fetchOne($sql, [$email]);
        if ($result && password_verify($password, $result['password_hash'])) {
            $this->id = $result['id'];
            $this->load();
            return true;
        }
        return false;
    }
    
    public function saveProfile($data) {
        // Переконатися, що JSON-поля закодовані
        if (isset($data['goals']) && is_array($data['goals'])) {
            $data['goals'] = json_encode($data['goals']);
        }
        if (isset($data['medical_restrictions']) && is_array($data['medical_restrictions'])) {
            $data['medical_restrictions'] = json_encode($data['medical_restrictions']);
        }
        $data['user_id'] = $this->id;
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Перевірка, чи існує профіль
        $exists = $this->db->fetchOne("SELECT user_id FROM user_profiles WHERE user_id = ?", [$this->id]);
        if ($exists) {
            unset($data['user_id']);
            $this->db->update('user_profiles', $data, 'user_id = :user_id', ['user_id' => $this->id]);
        } else {
            $this->db->insert('user_profiles', $data);
        }
        $this->load();
        return true;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function isAuthenticated() {
        return isset($this->data['id']) && $this->data['id'] > 0;
    }
    
    // Розрахунок BMR за формулою Міффліна-Жеора
    public function calculateBMR($weight, $height, $age, $gender) {
        if ($gender == 'male') {
            return 10 * $weight + 6.25 * $height - 5 * $age + 5;
        } else {
            return 10 * $weight + 6.25 * $height - 5 * $age - 161;
        }
    }
    
    // Максимальний пульс за формулою Танака
    public function getMaxHR($age) {
        return 208 - 0.7 * $age;
    }
    
    // Цільові зони пульсу за методом Карвонена
    public function getTargetHRZones($age, $restHR) {
        $maxHR = $this->getMaxHR($age);
        $reserve = $maxHR - $restHR;
        
        return [
            'warmup' => ['low' => $restHR + 0.5 * $reserve, 'high' => $restHR + 0.6 * $reserve],
            'fat_burn' => ['low' => $restHR + 0.6 * $reserve, 'high' => $restHR + 0.7 * $reserve],
            'aerobic' => ['low' => $restHR + 0.7 * $reserve, 'high' => $restHR + 0.8 * $reserve],
            'anaerobic' => ['low' => $restHR + 0.8 * $reserve, 'high' => $restHR + 0.9 * $reserve],
            'max' => ['low' => $restHR + 0.9 * $reserve, 'high' => $maxHR]
        ];
    }
}
?>