<?php
require_once 'controllers/ChatController.php';

$chat = new ChatController($userId);
$chats = $chat->getChats();
$selectedChat = (int)($_GET['chat_id'] ?? 0);
$messages = [];

if ($selectedChat) {
    $messages = $chat->getMessages($selectedChat);
    $chat->markAsRead($selectedChat);
}

// Получаем клиентов тренера для нового чата
require_once 'controllers/TrainerController.php';
$trainer = new TrainerController($userId);
$clients = $trainer->getClients('active');
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-chat-dots text-primary"></i> Чат з клієнтами
        </h1>
        <button class="btn btn-primary btn-gradient btn-sm" data-bs-toggle="modal" data-bs-target="#newChatModal">
            <i class="bi bi-plus-circle"></i> Новий чат
        </button>
    </div>

    <div class="row g-4">
        <!-- Список чатов -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0"><i class="bi bi-people"></i> Діалоги</h6>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <?php if (count($chats) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($chats as $c): ?>
                                <a href="/dashboard.php?page=chat&chat_id=<?php echo $c['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $selectedChat == $c['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($c['other_user_name']); ?></strong>
                                            <br>
                                            <small class="text-muted <?php echo $selectedChat == $c['id'] ? 'text-white-50' : ''; ?>">
                                                <?php echo htmlspecialchars(substr($c['last_message'] ?? '', 0, 30)) . (strlen($c['last_message'] ?? '') > 30 ? '...' : ''); ?>
                                            </small>
                                        </div>
                                        <?php if ($c['unread_count'] > 0): ?>
                                            <span class="badge bg-danger rounded-pill"><?php echo $c['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($c['last_message_time']): ?>
                                        <small class="text-muted <?php echo $selectedChat == $c['id'] ? 'text-white-50' : ''; ?>">
                                            <?php echo date('d.m.Y H:i', strtotime($c['last_message_time'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat display-6 d-block mb-2"></i>
                            <p>Немає діалогів</p>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newChatModal">
                                <i class="bi bi-plus-circle"></i> Почати чат
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Окно чата -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <?php if ($selectedChat): 
                            $otherUser = array_filter($chats, function($c) use ($selectedChat) {
                                return $c['id'] == $selectedChat;
                            });
                            $otherUser = reset($otherUser);
                        ?>
                            <i class="bi bi-person-circle"></i> 
                            <?php echo htmlspecialchars($otherUser['other_user_name'] ?? 'Клієнт'); ?>
                        <?php else: ?>
                            <i class="bi bi-chat"></i> Виберіть діалог
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="card-body" style="height: 450px; overflow-y: auto;" id="chatMessages">
                    <?php if ($selectedChat && count($messages) > 0): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="d-flex mb-3 <?php echo $msg['sender_id'] == $userId ? 'justify-content-end' : ''; ?>">
                                <div class="message-bubble <?php echo $msg['sender_id'] == $userId ? 'bg-primary text-white' : 'bg-light'; ?>" 
                                     style="max-width: 70%; padding: 10px 14px; border-radius: 12px;">
                                    <?php if ($msg['sender_id'] != $userId): ?>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($msg['sender_name']); ?></small>
                                    <?php endif; ?>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    <small class="<?php echo $msg['sender_id'] == $userId ? 'text-white-50' : 'text-muted'; ?> d-block text-end">
                                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($selectedChat): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat display-4 d-block mb-2"></i>
                            <p>Немає повідомлень</p>
                            <small>Напишіть перше повідомлення</small>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            <p>Виберіть діалог для перегляду повідомлень</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($selectedChat): ?>
                    <div class="card-footer bg-transparent border-0">
                        <form class="d-flex gap-2" id="chatForm">
                            <input type="hidden" name="chat_id" value="<?php echo $selectedChat; ?>">
                            <input type="text" name="message" class="form-control" placeholder="Напишіть повідомлення..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal новый чат -->
<div class="modal fade" id="newChatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus text-primary"></i> Новий чат</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Виберіть клієнта</label>
                    <select id="newChatUser" class="form-select">
                        <option value="">-- Виберіть --</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>">
                                <?php echo htmlspecialchars($client['full_name']); ?> (<?php echo htmlspecialchars($client['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" id="createChatBtn">
                    <i class="bi bi-plus-circle"></i> Почати чат
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Прокрутка чата вниз
    const chatContainer = document.getElementById('chatMessages');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Отправка сообщения
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'send');
            
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            fetch('/api/chat.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send"></i>';
                
                if (data.success) {
                    this.querySelector('input[name="message"]').value = '';
                    location.reload();
                } else {
                    showToast(data.error || 'Помилка відправки', 'danger');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send"></i>';
                showToast('Помилка з\'єднання', 'danger');
            });
        });
    }
    
    // Создание нового чата
    document.getElementById('createChatBtn').addEventListener('click', function() {
        const userId = document.getElementById('newChatUser').value;
        if (!userId) {
            showToast('Виберіть клієнта', 'warning');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('user_id', userId);
        
        fetch('/api/chat.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/dashboard.php?page=chat&chat_id=' + data.chat_id;
            } else {
                showToast(data.error || 'Помилка створення', 'danger');
            }
        })
        .catch(function() {
            showToast('Помилка з\'єднання', 'danger');
        });
    });
});
</script>