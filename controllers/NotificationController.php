<?php
require_once __DIR__ . '/../config/database.php';

class NotificationController {
    private $pdo;
    private $userId;
    
    public function __construct($userId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
    }
    
    /**
     * Создание уведомления
     */
    public function create($type, $title, $message, $icon = 'bi-bell', $link = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, icon, link)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$this->userId, $type, $title, $message, $icon, $link]);
    }
    
    /**
     * Создание уведомления по шаблону
     */
    public function createFromTemplate($templateCode, $params = []) {
        $stmt = $this->pdo->prepare("SELECT * FROM notification_templates WHERE code = ?");
        $stmt->execute([$templateCode]);
        $template = $stmt->fetch();
        
        if (!$template) {
            return false;
        }
        
        // Заменяем параметры в сообщении
        $title = $template['title'];
        $message = $template['message'];
        
        foreach ($params as $key => $value) {
            $title = str_replace('{' . $key . '}', $value, $title);
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        return $this->create(
            $template['type'],
            $title,
            $message,
            $template['icon'],
            $params['link'] ?? null
        );
    }
    
    /**
     * Получение всех уведомлений пользователя
     */
    public function getAll($limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$this->userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение непрочитанных уведомлений
     */
    public function getUnread($limit = 20) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$this->userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Количество непрочитанных уведомлений
     */
    public function getUnreadCount() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$this->userId]);
        return (int)($stmt->fetch()['count'] ?? 0);
    }
    
    /**
     * Отметить уведомление как прочитанное
     */
    public function markAsRead($notificationId) {
        $stmt = $this->pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$notificationId, $this->userId]);
    }
    
    /**
     * Отметить все уведомления как прочитанные
     */
    public function markAllAsRead() {
        $stmt = $this->pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = ? AND is_read = 0
        ");
        return $stmt->execute([$this->userId]);
    }
    
    /**
     * Удалить уведомление
     */
    public function delete($notificationId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM notifications 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$notificationId, $this->userId]);
    }
    
    /**
     * Удалить все уведомления
     */
    public function deleteAll() {
        $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
        return $stmt->execute([$this->userId]);
    }
    
    /**
     * Получение уведомлений для API (с пагинацией)
     */
    public function getApiNotifications($limit = 20, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$this->userId, $limit, $offset]);
        return $stmt->fetchAll();
    }
}

/**
 * Вспомогательная функция для быстрого создания уведомления
 */
function createNotification($userId, $type, $title, $message, $icon = 'bi-bell', $link = null) {
    $controller = new NotificationController($userId);
    return $controller->create($type, $title, $message, $icon, $link);
}
?>