<?php
require_once 'controllers/TrainerController.php';

$trainer = new TrainerController($userId);
$clients = $trainer->getClients('active');

$selectedClient = (int)($_GET['client_id'] ?? 0);
$messages = [];

if ($selectedClient) {
    $messages = $trainer->getMessages($selectedClient);
    $trainer->markMessagesAsRead($selectedClient);
}
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-chat text-primary"></i> Повідомлення
        </h1>
    </div>

    <div class="row g-4">
        <!-- Список клиентов -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0"><i class="bi bi-people"></i> Клієнти</h6>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    <?php if (count($clients) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($clients as $client): ?>
                                <a href="/dashboard.php?page=messages&client_id=<?php echo $client['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $selectedClient == $client['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($client['full_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($client['email']); ?></small>
                                        </div>
                                        <?php if ($client['unread_messages'] > 0): ?>
                                            <span class="badge bg-danger rounded-pill"><?php echo $client['unread_messages']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-people display-6 d-block mb-2"></i>
                            <p>Немає клієнтів</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Чат -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <?php if ($selectedClient): ?>
                            <?php 
                            $client = array_filter($clients, function($c) use ($selectedClient) {
                                return $c['id'] == $selectedClient;
                            });
                            $client = reset($client);
                            ?>
                            <i class="bi bi-person-circle"></i> 
                            <?php echo htmlspecialchars($client['full_name'] ?? 'Клієнт'); ?>
                        <?php else: ?>
                            <i class="bi bi-chat"></i> Виберіть клієнта
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="card-body" style="height: 400px; overflow-y: auto;" id="chatMessages">
                    <?php if ($selectedClient && count($messages) > 0): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="d-flex mb-3 <?php echo $msg['sender_id'] == $userId ? 'justify-content-end' : ''; ?>">
                                <div class="message-bubble <?php echo $msg['sender_id'] == $userId ? 'bg-primary text-white' : 'bg-light'; ?>" 
                                     style="max-width: 70%; padding: 10px 14px; border-radius: 12px;">
                                    <small class="<?php echo $msg['sender_id'] == $userId ? 'text-white-50' : 'text-muted'; ?> d-block">
                                        <?php echo $msg['sender_id'] == $userId ? 'Ви' : $msg['sender_name']; ?>
                                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                    </small>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($selectedClient): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat display-4 d-block mb-2"></i>
                            <p>Немає повідомлень</p>
                            <small>Напишіть перше повідомлення</small>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            <p>Виберіть клієнта для перегляду повідомлень</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($selectedClient): ?>
                    <div class="card-footer bg-transparent border-0">
                        <form class="d-flex gap-2" id="messageForm">
                            <input type="hidden" name="client_id" value="<?php echo $selectedClient; ?>">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Прокрутка чата вниз
    const chatContainer = document.getElementById('chatMessages');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Отправка сообщения
    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'send_message');
            
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            fetch('/api/trainer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send"></i>';
                
                if (data.success) {
                    // Очищаем поле
                    this.querySelector('input[name="message"]').value = '';
                    // Обновляем чат
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
});
</script>