<?php
require_once __DIR__ . '/../config/database.php';

class ChatController {
    private $pdo;
    private $userId;
    
    public function __construct($userId) {
        global $pdo;
        $this->pdo = $pdo;
        $this->userId = $userId;
    }
    
    /**
     * Получение или создание чата
     */
    public function getOrCreateChat($otherUserId) {
        // Проверяем существование чата
        $stmt = $this->pdo->prepare("
            SELECT id FROM chats 
            WHERE (user1_id = ? AND user2_id = ?) 
               OR (user1_id = ? AND user2_id = ?)
        ");
        $stmt->execute([$this->userId, $otherUserId, $otherUserId, $this->userId]);
        $chat = $stmt->fetch();
        
        if ($chat) {
            return $chat['id'];
        }
        
        // Создаем новый чат
        $stmt = $this->pdo->prepare("
            INSERT INTO chats (user1_id, user2_id) VALUES (?, ?)
        ");
        $stmt->execute([$this->userId, $otherUserId]);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Получение списка чатов пользователя
     */
    public function getChats() {
        $stmt = $this->pdo->prepare("
            SELECT 
                c.*,
                CASE 
                    WHEN c.user1_id = ? THEN u2.full_name 
                    ELSE u1.full_name 
                END as other_user_name,
                CASE 
                    WHEN c.user1_id = ? THEN u2.id 
                    ELSE u1.id 
                END as other_user_id,
                (SELECT message FROM chat_messages 
                 WHERE chat_id = c.id 
                 ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM chat_messages 
                 WHERE chat_id = c.id 
                 ORDER BY created_at DESC LIMIT 1) as last_message_time,
                (SELECT COUNT(*) FROM chat_messages 
                 WHERE chat_id = c.id AND sender_id != ? AND is_read = 0) as unread_count
            FROM chats c
            JOIN users u1 ON c.user1_id = u1.id
            JOIN users u2 ON c.user2_id = u2.id
            WHERE c.user1_id = ? OR c.user2_id = ?
            ORDER BY c.updated_at DESC
        ");
        $stmt->execute([$this->userId, $this->userId, $this->userId, $this->userId, $this->userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение сообщений чата
     * ИСПРАВЛЕНО: LIMIT вставляется напрямую
     */
    public function getMessages($chatId, $limit = 50) {
        $limit = max(1, (int)$limit);
        
        $stmt = $this->pdo->prepare("
            SELECT cm.*, u.full_name as sender_name
            FROM chat_messages cm
            JOIN users u ON cm.sender_id = u.id
            WHERE cm.chat_id = ?
            ORDER BY cm.created_at DESC
            LIMIT " . $limit . "
        ");
        $stmt->execute([$chatId]);
        return array_reverse($stmt->fetchAll());
    }
    
    /**
     * Отправка сообщения
     */
    public function sendMessage($chatId, $message) {
        // Проверяем, что пользователь участник чата
        $stmt = $this->pdo->prepare("
            SELECT id FROM chats WHERE id = ? AND (user1_id = ? OR user2_id = ?)
        ");
        $stmt->execute([$chatId, $this->userId, $this->userId]);
        if (!$stmt->fetch()) {
            return false;
        }
        
        // Добавляем сообщение
        $stmt = $this->pdo->prepare("
            INSERT INTO chat_messages (chat_id, sender_id, message) VALUES (?, ?, ?)
        ");
        $success = $stmt->execute([$chatId, $this->userId, $message]);
        
        if ($success) {
            // Обновляем время чата
            $stmt = $this->pdo->prepare("UPDATE chats SET updated_at = NOW() WHERE id = ?");
            $stmt->execute([$chatId]);
        }
        
        return $success;
    }
    
    /**
     * Отметить сообщения как прочитанные
     */
    public function markAsRead($chatId) {
        $stmt = $this->pdo->prepare("
            UPDATE chat_messages 
            SET is_read = 1 
            WHERE chat_id = ? AND sender_id != ? AND is_read = 0
        ");
        return $stmt->execute([$chatId, $this->userId]);
    }
    
    /**
     * Получение непрочитанных сообщений
     */
    public function getUnreadCount() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total 
            FROM chat_messages cm
            JOIN chats c ON cm.chat_id = c.id
            WHERE (c.user1_id = ? OR c.user2_id = ?) 
              AND cm.sender_id != ? 
              AND cm.is_read = 0
        ");
        $stmt->execute([$this->userId, $this->userId, $this->userId]);
        return $stmt->fetch()['total'] ?? 0;
    }
}
?>