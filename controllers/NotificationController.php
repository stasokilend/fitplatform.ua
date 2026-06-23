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

    /**
     * Получение единой ленты уведомлений: системные события + непрочитанные сообщения чата.
     */
    public function getNotificationFeed($limit = 20, $offset = 0) {
        $notifications = $this->getApiNotifications($limit, $offset);
        $feed = [];

        foreach ($notifications as $item) {
            $type = $this->normalizeType($item);
            $item['kind'] = 'notification';
            $item['feed_id'] = 'notification-' . $item['id'];
            $item['type'] = $type;
            $item['meta'] = $this->getTypeLabel($type);
            $feed[] = $item;
        }

        if ($offset === 0) {
            foreach ($this->getUnreadChatNotifications() as $chatItem) {
                $feed[] = $chatItem;
            }
        }

        usort($feed, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return array_slice($feed, 0, $limit);
    }

    /**
     * Количество непрочитанных элементов в объединенной ленте.
     */
    public function getFeedUnreadCount() {
        $chatCount = 0;
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total
            FROM chat_messages cm
            JOIN chats c ON cm.chat_id = c.id
            WHERE (c.user1_id = ? OR c.user2_id = ?)
              AND cm.sender_id != ?
              AND cm.is_read = 0
        ");
        $stmt->execute([$this->userId, $this->userId, $this->userId]);
        $chatCount = (int)($stmt->fetch()['total'] ?? 0);

        return $this->getUnreadCount() + $chatCount;
    }

    private function getUnreadChatNotifications() {
        $stmt = $this->pdo->prepare("
            SELECT
                cm.id,
                cm.chat_id,
                cm.message,
                cm.created_at,
                u.full_name as sender_name
            FROM chat_messages cm
            JOIN chats c ON cm.chat_id = c.id
            JOIN users u ON cm.sender_id = u.id
            JOIN (
                SELECT MAX(cm2.id) as latest_id
                FROM chat_messages cm2
                JOIN chats c2 ON cm2.chat_id = c2.id
                WHERE (c2.user1_id = ? OR c2.user2_id = ?)
                  AND cm2.sender_id != ?
                  AND cm2.is_read = 0
                GROUP BY cm2.chat_id
            ) latest ON latest.latest_id = cm.id
            WHERE (c.user1_id = ? OR c.user2_id = ?)
            ORDER BY cm.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$this->userId, $this->userId, $this->userId, $this->userId, $this->userId]);

        return array_map(function ($message) {
            return [
                'id' => $message['id'],
                'kind' => 'chat',
                'feed_id' => 'chat-' . $message['id'],
                'type' => 'message',
                'title' => 'Нове повідомлення від ' . $message['sender_name'],
                'message' => $this->shorten($message['message'], 120),
                'icon' => 'bi-chat-dots-fill',
                'link' => '/dashboard.php?page=chat&chat_id=' . (int)$message['chat_id'],
                'is_read' => 0,
                'created_at' => $message['created_at'],
                'meta' => 'Повідомлення з чату'
            ];
        }, $stmt->fetchAll());
    }

    private function normalizeType($item) {
        $type = $item['type'] ?? 'info';

        if ($type === 'achievement') {
            return 'achievement';
        }

        $text = mb_strtolower(($item['title'] ?? '') . ' ' . ($item['message'] ?? ''), 'UTF-8');
        if ($type === 'info' && (strpos($text, 'програм') !== false || strpos($text, 'тренер') !== false)) {
            return 'program';
        }

        return $type ?: 'info';
    }

    private function getTypeLabel($type) {
        $labels = [
            'message' => 'Повідомлення з чату',
            'achievement' => 'Досягнення',
            'program' => 'Програма тренера',
            'success' => 'Успішна дія',
            'warning' => 'Важливо',
            'danger' => 'Помилка',
            'info' => 'Сповіщення'
        ];

        return $labels[$type] ?? 'Сповіщення';
    }

    private function shorten($text, $length) {
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 1, 'UTF-8') . '…';
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
