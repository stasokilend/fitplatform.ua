<?php
require_once 'controllers/TrainerController.php';
require_once 'controllers/ChatController.php';

$clientId = (int)($_GET['id'] ?? 0);
if (!$clientId) {
    header('Location: /dashboard.php?page=clients');
    exit;
}

$trainer = new TrainerController($userId);
$client = $trainer->getClient($clientId);

if (!$client) {
    header('Location: /dashboard.php?page=clients');
    exit;
}

$progress = $trainer->getClientProgress($clientId);
$chat = new ChatController($userId);
$chatId = $chat->getOrCreateChat($clientId);
$messages = $chat->getMessages($chatId, 30);
$chat->markAsRead($chatId);

$workoutStats = $progress['workout_stats'] ?? [];
$totalWorkouts = (int)($workoutStats['total_workouts'] ?? 0);
$completedWorkouts = (int)($workoutStats['completed_workouts'] ?? 0);
$completionRate = $totalWorkouts > 0 ? (int)round(($completedWorkouts / $totalWorkouts) * 100) : 0;
$totalMinutes = (int)round($workoutStats['total_minutes'] ?? 0);
$avgDuration = (int)round($workoutStats['avg_duration'] ?? 0);
$currentProgram = $progress['current_program'] ?? null;
$programProgress = $currentProgram ? max(0, min(100, (int)($currentProgram['progress'] ?? 0))) : 0;
$currentWeight = $client['weight'] ?? null;
$targetWeight = $client['target_weight'] ?? null;
$weightDelta = ($currentWeight && $targetWeight) ? round((float)$currentWeight - (float)$targetWeight, 1) : null;
$clientName = $client['full_name'] ?? 'К';
$clientInitial = function_exists('mb_substr') && function_exists('mb_strtoupper')
    ? mb_strtoupper(mb_substr($clientName, 0, 1))
    : strtoupper(substr($clientName, 0, 1));

if (!function_exists('renderClientDetailValue')) {
    function renderClientDetailValue($value, $suffix = '') {
        if ($value === null || $value === '') {
            return '<span class="text-muted">—</span>';
        }

        return htmlspecialchars((string)$value) . $suffix;
    }
}
?>

<div class="fade-in-up client-detail-page">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center gap-3 pb-3 mb-4 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; font-size: 1.8rem; flex-shrink: 0;">
                    <?php echo htmlspecialchars($clientInitial); ?>
                </div>
                <div>
                    <h1 class="h2 mb-1"><?php echo htmlspecialchars($client['full_name']); ?></h1>
                    <div class="text-muted small">
                        <i class="bi bi-calendar-check"></i> Клієнт з <?php echo date('d.m.Y', strtotime($client['assigned_at'])); ?>
                        <?php if (($client['unread_messages'] ?? 0) > 0): ?>
                            <span class="badge bg-danger ms-2"><?php echo (int)$client['unread_messages']; ?> нових</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="/dashboard.php?page=chat&chat_id=<?php echo (int)$chatId; ?>" class="btn btn-outline-primary">
                <i class="bi bi-chat-dots"></i> Відкрити чат
            </a>
            <a href="/dashboard.php?page=clients" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-person-vcard text-primary"></i> Профіль клієнта</h5>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 d-flex justify-content-between gap-3"><span class="text-muted">Email</span><strong class="text-end"><?php echo htmlspecialchars($client['email']); ?></strong></div>
                        <div class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Вік</span><strong><?php echo renderClientDetailValue($client['age'] ?? null, ' років'); ?></strong></div>
                        <div class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Вага</span><strong><?php echo renderClientDetailValue($currentWeight, ' кг'); ?></strong></div>
                        <div class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Зріст</span><strong><?php echo renderClientDetailValue($client['height'] ?? null, ' см'); ?></strong></div>
                        <div class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Рівень</span><strong><?php echo getFitnessLevelLabel($client['fitness_level']); ?></strong></div>
                        <div class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Мета</span><strong class="text-end"><?php echo getGoalTypeLabel($client['goal_type']); ?></strong></div>
                    </div>

                    <?php if ($targetWeight): ?>
                        <div class="alert alert-primary bg-primary-subtle border-0 mt-3 mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-bullseye"></i> Цільова вага</span>
                                <strong><?php echo htmlspecialchars((string)$targetWeight); ?> кг</strong>
                            </div>
                            <?php if ($weightDelta !== null): ?>
                                <small class="text-muted d-block mt-1">
                                    <?php echo $weightDelta === 0.0 ? 'Ціль досягнуто' : 'До цілі: ' . htmlspecialchars((string)abs($weightDelta)) . ' кг'; ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Тренувань</div><div class="display-6 text-primary"><?php echo $totalWorkouts; ?></div></div></div></div>
                <div class="col-sm-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Завершено</div><div class="display-6 text-success"><?php echo $completedWorkouts; ?></div></div></div></div>
                <div class="col-sm-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Виконання</div><div class="display-6 text-warning"><?php echo $completionRate; ?>%</div></div></div></div>
                <div class="col-sm-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Хвилин</div><div class="display-6 text-info"><?php echo $totalMinutes; ?></div><small class="text-muted">~<?php echo $avgDuration; ?> хв/трен.</small></div></div></div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-clipboard2-pulse text-primary"></i> Поточна програма</h5>
                    <?php if ($currentProgram): ?>
                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                            <strong><?php echo htmlspecialchars($currentProgram['program_name']); ?></strong>
                            <span class="badge bg-primary-subtle text-primary"><?php echo $programProgress; ?>%</span>
                        </div>
                        <div class="text-muted small mb-3"><?php echo (int)$currentProgram['duration_weeks']; ?> тижнів • <?php echo (int)$currentProgram['sessions_per_week']; ?> тренувань/тиждень</div>
                        <div class="progress" style="height: 10px;"><div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $programProgress; ?>%;" aria-valuenow="<?php echo $programProgress; ?>" aria-valuemin="0" aria-valuemax="100"></div></div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4"><i class="bi bi-file-earmark-plus display-6 d-block mb-2"></i><p class="mb-0">Активну програму ще не призначено</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-journal-medical text-primary"></i> Нотатки та обмеження</h5>
                    <div class="row g-3">
                        <div class="col-md-4"><small class="text-muted">Нотатки тренера</small><p class="mb-0"><?php echo nl2br(htmlspecialchars($client['notes'] ?: '—')); ?></p></div>
                        <div class="col-md-4"><small class="text-muted">Цілі</small><p class="mb-0"><?php echo nl2br(htmlspecialchars($client['goals'] ?: '—')); ?></p></div>
                        <div class="col-md-4"><small class="text-muted">Медичні примітки</small><p class="mb-0"><?php echo nl2br(htmlspecialchars(($client['health_conditions'] ?: $client['medical_notes']) ?: '—')); ?></p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-chat text-primary"></i> Останні повідомлення</h5>
            <small class="text-muted"><?php echo count($messages); ?> з останніх повідомлень</small>
        </div>
        <div class="card-body" id="clientMessages" style="max-height: 360px; overflow-y: auto;">
            <?php if (count($messages) > 0): ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isOwnMessage = (int)$msg['sender_id'] === (int)$userId; ?>
                    <div class="d-flex mb-3 <?php echo $isOwnMessage ? 'justify-content-end' : ''; ?>">
                        <div class="message-bubble <?php echo $isOwnMessage ? 'bg-primary text-white' : 'bg-light'; ?>" style="max-width: 75%; padding: 10px 14px; border-radius: 14px;">
                            <small class="<?php echo $isOwnMessage ? 'text-white-50' : 'text-muted'; ?> d-block mb-1"><?php echo $isOwnMessage ? 'Ви' : htmlspecialchars($msg['sender_name']); ?> • <?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?></small>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-4"><i class="bi bi-chat display-6 d-block mb-2"></i><p class="mb-0">Повідомлень ще немає</p></div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-transparent border-0">
            <form class="d-flex gap-2" id="messageForm">
                <input type="hidden" name="chat_id" value="<?php echo (int)$chatId; ?>">
                <input type="text" name="message" class="form-control" placeholder="Напишіть повідомлення..." required autocomplete="off">
                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.getElementById('clientMessages');
    if (messages) {
        messages.scrollTop = messages.scrollHeight;
    }

    const messageForm = document.getElementById('messageForm');
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'send');

        const button = this.querySelector('button[type="submit"]');
        const input = this.querySelector('input[name="message"]');
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch('/api/chat.php', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                input.value = '';
                location.reload();
                return;
            }

            showToast(data.error || 'Помилка відправки', 'danger');
        })
        .catch(function() {
            showToast('Помилка з\'єднання', 'danger');
        })
        .finally(function() {
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-send"></i>';
        });
    });
});
</script>
