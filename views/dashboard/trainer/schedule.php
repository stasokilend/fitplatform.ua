<?php
require_once 'controllers/TrainerController.php';

$trainer = new TrainerController($userId);
$weekStart = $_GET['week'] ?? date('Y-m-d');
$schedule = $trainer->getSchedule($weekStart);

// Получаем клиентов для выбора
$clients = $trainer->getClients('active');

$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
$dayLabels = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];

// Группировка расписания по дням
$scheduleByDay = [];
foreach ($schedule as $event) {
    $day = strtolower($event['day_of_week']);
    if (!isset($scheduleByDay[$day])) {
        $scheduleByDay[$day] = [];
    }
    $scheduleByDay[$day][] = $event;
}

$statusLabels = [
    'available' => 'Доступно',
    'booked' => 'Заброньовано',
    'completed' => 'Завершено',
    'cancelled' => 'Скасовано'
];

$statusColors = [
    'available' => 'success',
    'booked' => 'warning',
    'completed' => 'info',
    'cancelled' => 'danger'
];
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-calendar text-primary"></i> Розклад
        </h1>
        <div>
            <button class="btn btn-primary btn-gradient" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                <i class="bi bi-plus-circle"></i> Додати заняття
            </button>
        </div>
    </div>

    <!-- Навигация по неделям -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="btn-group">
            <a href="/dashboard.php?page=schedule&week=<?php echo date('Y-m-d', strtotime($weekStart . ' -7 days')); ?>" 
               class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="btn btn-secondary disabled">
                <?php echo date('d.m.Y', strtotime($weekStart)); ?> - 
                <?php echo date('d.m.Y', strtotime($weekStart . ' +6 days')); ?>
            </span>
            <a href="/dashboard.php?page=schedule&week=<?php echo date('Y-m-d', strtotime($weekStart . ' +7 days')); ?>" 
               class="btn btn-outline-secondary">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <a href="/dashboard.php?page=schedule&week=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-calendar2-week"></i> Сьогодні
        </a>
    </div>

    <!-- Календарь -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr class="text-center">
                        <th style="width: 80px;">Час</th>
                        <?php foreach ($days as $day): ?>
                            <th>
                                <?php echo $dayLabels[array_search($day, $days)]; ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo date('d.m', strtotime($weekStart . ' +' . array_search($day, $days) . ' days')); ?>
                                </small>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($hour = 6; $hour <= 22; $hour++): 
                        $timeSlot = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                    ?>
                        <tr>
                            <td class="text-center text-muted small" style="background: #f8f9fa;">
                                <?php echo $timeSlot; ?>
                            </td>
                            <?php foreach ($days as $day): 
                                $found = false;
                                $events = $scheduleByDay[$day] ?? [];
                            ?>
                                <td style="height: 50px; cursor: pointer;" 
                                    onclick="quickAddEvent('<?php echo $day; ?>', '<?php echo $timeSlot; ?>')">
                                    <?php foreach ($events as $event): 
                                        $eventHour = date('H', strtotime($event['start_time']));
                                        if ($eventHour == $hour): 
                                            $found = true;
                                    ?>
                                        <div class="schedule-event bg-<?php echo $statusColors[$event['status']]; ?> text-white p-1 rounded-2 small"
                                             style="cursor: pointer;"
                                             onclick="event.stopPropagation(); viewEvent(<?php echo $event['id']; ?>)">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="bi bi-person"></i>
                                                    <?php echo htmlspecialchars($event['client_name'] ?? 'Вільно'); ?>
                                                </span>
                                                <span class="badge bg-white text-dark">
                                                    <?php echo date('H:i', strtotime($event['start_time'])); ?>-
                                                    <?php echo date('H:i', strtotime($event['end_time'])); ?>
                                                </span>
                                            </div>
                                            <?php if ($event['notes']): ?>
                                                <div class="small text-white-50"><?php echo htmlspecialchars(substr($event['notes'], 0, 30)); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if (!$found): ?>
                                        <div class="text-muted small text-center">-</div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Легенда -->
    <div class="mt-3 d-flex gap-3 flex-wrap">
        <?php foreach ($statusLabels as $key => $label): ?>
            <span class="badge bg-<?php echo $statusColors[$key]; ?> px-3 py-2">
                <?php echo $label; ?>
            </span>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===== МОДАЛЬНОЕ ОКНО ДОБАВЛЕНИЯ ===== -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle text-primary"></i> Додати заняття
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm">
                <div class="modal-body">
                    <input type="hidden" id="scheduleAction" value="add">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Клієнт</label>
                            <select id="scheduleClient" class="form-select" required>
                                <option value="">-- Виберіть клієнта --</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>">
                                        <?php echo htmlspecialchars($client['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                <?php echo count($clients) > 0 ? count($clients) . ' активних клієнтів' : 'Немає активних клієнтів'; ?>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">День тижня</label>
                            <select id="scheduleDay" class="form-select" required>
                                <option value="monday">Понеділок</option>
                                <option value="tuesday">Вівторок</option>
                                <option value="wednesday">Середа</option>
                                <option value="thursday">Четвер</option>
                                <option value="friday">П'ятниця</option>
                                <option value="saturday">Субота</option>
                                <option value="sunday">Неділя</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Час початку</label>
                            <input type="time" id="scheduleStartTime" class="form-control" value="09:00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Час завершення</label>
                            <input type="time" id="scheduleEndTime" class="form-control" value="10:00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Статус</label>
                            <select id="scheduleStatus" class="form-select">
                                <option value="available">Доступно</option>
                                <option value="booked" selected>Заброньовано</option>
                                <option value="completed">Завершено</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Дата (опціонально)</label>
                            <input type="date" id="scheduleDate" class="form-control">
                            <small class="text-muted">Залиште порожнім для повторюваного заняття</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Нотатки</label>
                            <textarea id="scheduleNotes" class="form-control" rows="2" placeholder="Додаткова інформація про заняття..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Додати
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== МОДАЛЬНОЕ ОКНО ПРОСМОТРА/РЕДАКТИРОВАНИЯ ===== -->
<div class="modal fade" id="viewScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-event text-primary"></i> Деталі заняття
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewScheduleBody">
                <!-- Заполняется JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
                <button type="button" class="btn btn-danger" id="deleteScheduleBtn">
                    <i class="bi bi-trash"></i> Видалити
                </button>
                <button type="button" class="btn btn-primary" id="editScheduleBtn">
                    <i class="bi bi-pencil"></i> Редагувати
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== ДОБАВЛЕНИЕ ЗАНЯТИЯ =====
    document.getElementById('scheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('action', 'add_schedule');
        formData.append('client_id', document.getElementById('scheduleClient').value);
        formData.append('day_of_week', document.getElementById('scheduleDay').value);
        formData.append('start_time', document.getElementById('scheduleStartTime').value);
        formData.append('end_time', document.getElementById('scheduleEndTime').value);
        formData.append('status', document.getElementById('scheduleStatus').value);
        formData.append('date', document.getElementById('scheduleDate').value || '');
        formData.append('notes', document.getElementById('scheduleNotes').value);
        
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Додавання...';
        
        fetch('/api/trainer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Додати';
            
            if (data.success) {
                showToast('Заняття додано!', 'success');
                const modal = bootstrap.Modal.getInstance(document.getElementById('addScheduleModal'));
                modal.hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Помилка додавання', 'danger');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Додати';
            showToast('Помилка з\'єднання', 'danger');
        });
    });
    
    // ===== БЫСТРОЕ ДОБАВЛЕНИЕ ИЗ КАЛЕНДАРЯ =====
    window.quickAddEvent = function(day, time) {
        const modal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
        document.getElementById('scheduleDay').value = day;
        document.getElementById('scheduleStartTime').value = time;
        // Устанавливаем время окончания +1 час
        const endHour = parseInt(time) + 1;
        document.getElementById('scheduleEndTime').value = String(endHour).padStart(2, '0') + ':00';
        modal.show();
    };
    
    // ===== ПРОСМОТР ЗАНЯТИЯ =====
    let currentEventId = null;
    
    window.viewEvent = function(eventId) {
        currentEventId = eventId;
        
        // Получаем данные события
        const eventElement = document.querySelector(`.schedule-event[data-id="${eventId}"]`);
        if (eventElement) {
            // Показываем модальное окно с информацией
            const body = document.getElementById('viewScheduleBody');
            body.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Завантаження...</span>
                    </div>
                </div>
            `;
            
            // Здесь можно загрузить детали через API
            // Показываем заглушку
            body.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Деталі заняття будуть доступні в наступній версії
                </div>
                <p><strong>ID:</strong> ${eventId}</p>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('viewScheduleModal'));
            modal.show();
        }
    };
    
    // ===== УДАЛЕНИЕ ЗАНЯТИЯ =====
    document.getElementById('deleteScheduleBtn').addEventListener('click', function() {
        if (!currentEventId) return;
        if (!confirm('Видалити це заняття?')) return;
        
        const formData = new FormData();
        formData.append('action', 'delete_schedule');
        formData.append('schedule_id', currentEventId);
        
        fetch('/api/trainer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Заняття видалено', 'success');
                const modal = bootstrap.Modal.getInstance(document.getElementById('viewScheduleModal'));
                modal.hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Помилка видалення', 'danger');
            }
        })
        .catch(function() {
            showToast('Помилка з\'єднання', 'danger');
        });
    });
});
</script>

<style>
/* Стили для событий в календаре */
.schedule-event {
    padding: 4px 8px;
    margin: 2px 0;
    border-radius: 4px;
    font-size: 0.75rem;
    transition: all 0.2s ease;
}

.schedule-event:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.schedule-event .badge {
    font-size: 0.6rem;
}

.schedule-event.bg-available {
    background: #d4edda !important;
    color: #155724 !important;
}

.schedule-event.bg-booked {
    background: #fff3cd !important;
    color: #856404 !important;
}

.schedule-event.bg-completed {
    background: #cce5ff !important;
    color: #004085 !important;
}

.schedule-event.bg-cancelled {
    background: #f8d7da !important;
    color: #721c24 !important;
}

/* Таблица календаря */
.table-bordered td {
    vertical-align: middle;
    padding: 4px;
    min-height: 50px;
}

.table-bordered td .schedule-event {
    cursor: pointer;
}

/* Адаптивность для мобильных */
@media (max-width: 768px) {
    .table-bordered td {
        min-height: 40px;
        padding: 2px;
        font-size: 0.7rem;
    }
    
    .schedule-event {
        font-size: 0.6rem;
        padding: 2px 4px;
    }
    
    .schedule-event .badge {
        font-size: 0.5rem;
    }
}
</style>