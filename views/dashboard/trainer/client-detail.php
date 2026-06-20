<?php
require_once 'controllers/TrainerController.php';

$clientId = (int)($_GET['id'] ?? 0);
if (!$clientId) {
    header('Location: /dashboard.php?page=clients');
    exit;
}

$trainer = new TrainerController($userId);
$client = $trainer->getClient($clientId);
$progress = $trainer->getClientProgress($clientId);

if (!$client) {
    header('Location: /dashboard.php?page=clients');
    exit;
}

// Получаем сообщения
$messages = $trainer->getMessages($clientId);
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h2">
                <i class="bi bi-person text-primary"></i> <?php echo htmlspecialchars($client['full_name']); ?>
            </h1>
            <small class="text-muted">
                Клієнт з <?php echo date('d.m.Y', strtotime($client['assigned_at'])); ?>
            </small>
        </div>
        <a href="/dashboard.php?page=clients" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row g-4">
        <!-- Информация -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <?php echo strtoupper(substr($client['full_name'], 0, 1)); ?>
                    </div>
                    <h5 class="mb-0"><?php echo htmlspecialchars($client['full_name']); ?></h5>
                    <small class="text-muted"><?php echo htmlspecialchars($client['email']); ?></small>
                    
                    <hr>
                    
                    <div class="row text-start">
                        <div class="col-6">
                            <small class="text-muted">Вік</small>
                            <p class="fw-semibold"><?php echo $client['age'] ?? '-'; ?> років</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Вага</small>
                            <p class="fw-semibold"><?php echo $client['weight'] ?? '-'; ?> кг</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Зріст</small>
                            <p class="fw-semibold"><?php echo $client['height'] ?? '-'; ?> см</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Рівень</small>
                            <p class="fw-semibold"><?php echo getFitnessLevelLabel($client['fitness_level']); ?></p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Мета</small>
                            <p class="fw-semibold"><?php echo getGoalTypeLabel($client['goal_type']); ?></p>
                        </div>
                        <?php if ($client['target_weight']): ?>
                            <div class="col-12">
                                <small class="text-muted">Цільова вага</small>
                                <p class="fw-semibold"><?php echo $client['target_weight']; ?> кг</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Прогресс -->
        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-6">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="display-6 text-primary"><?php echo $progress['workout_stats']['total_workouts'] ?? 0; ?></div>
                            <small class="text-muted">Всього тренувань</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="display-6 text-success"><?php echo $progress['workout_stats']['completed_workouts'] ?? 0; ?></div>
                            <small class="text-muted">Завершено</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="display-6 text-warning"><?php echo round($progress['workout_stats']['avg_duration'] ?? 0); ?></div>
                            <small class="text-muted">Середня тривалість (хв)</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="display-6 text-info"><?php echo round($progress['workout_stats']['total_minutes'] ?? 0); ?></div>
                            <small class="text-muted">Всього хвилин</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Текущая программа -->
            <?php if ($progress['current_program']): ?>
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">
                        <h6 class="mb-2"><i class="bi bi-file-text text-primary"></i> Поточна програма</h6>
                        <p class="fw-semibold mb-0"><?php echo htmlspecialchars($progress['current_program']['program_name']); ?></p>
                        <small class="text-muted">
                            <?php echo $progress['current_program']['duration_weeks']; ?> тижнів • 
                            <?php echo $progress['current_program']['sessions_per_week']; ?> тренувань/тиждень
                        </small>
                        <div class="mt-2">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?php echo $progress['current_program']['progress'] ?? 0; ?>%;"></div>
                            </div>
                            <small class="text-muted">Прогрес: <?php echo $progress['current_program']['progress'] ?? 0; ?>%</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Сообщения -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0"><i class="bi bi-chat text-primary"></i> Повідомлення</h5>
        </div>
        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
            <?php if (count($messages) > 0): ?>
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
            <?php else: ?>
                <div class="text-center text-muted py-3">
                    <i class="bi bi-chat display-6 d-block mb-2"></i>
                    <p>Немає повідомлень</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-transparent border-0">
            <form class="d-flex gap-2" id="messageForm">
                <input type="hidden" name="client_id" value="<?php echo $clientId; ?>">
                <input type="text" name="message" class="form-control" placeholder="Напишіть повідомлення..." required>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Отправка сообщения
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'send_message');
        
        fetch('/api/trainer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showToast(data.error || 'Помилка відправки', 'danger');
            }
        })
        .catch(function() {
            showToast('Помилка з\'єднання', 'danger');
        });
    });
});
</script>