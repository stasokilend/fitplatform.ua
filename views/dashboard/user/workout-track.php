<?php
require_once 'config/database.php';
require_once 'controllers/WorkoutGenerator.php';

$userId = $_SESSION['user_id'];
$planId = (int)($_GET['id'] ?? 0);

if (!$planId) {
    header('Location: /dashboard.php?page=workouts');
    exit;
}

// Получаем данные тренировки
$stmt = $pdo->prepare("
    SELECT w.*, 
           COUNT(pe.id) as total_exercises,
           COUNT(CASE WHEN pe.is_completed = 1 THEN 1 END) as completed_exercises
    FROM workout_plans w
    LEFT JOIN plan_exercises pe ON w.id = pe.plan_id
    WHERE w.id = ? AND w.user_id = ?
    GROUP BY w.id
");
$stmt->execute([$planId, $userId]);
$workout = $stmt->fetch();

if (!$workout) {
    header('Location: /dashboard.php?page=workouts');
    exit;
}

// Получаем упражнения
$stmt = $pdo->prepare("
    SELECT pe.*, e.name, e.muscle_group, e.difficulty, e.description
    FROM plan_exercises pe
    JOIN exercises e ON pe.exercise_id = e.id
    WHERE pe.plan_id = ?
    ORDER BY pe.order_num
");
$stmt->execute([$planId]);
$exercises = $stmt->fetchAll();

$progress = $workout['total_exercises'] > 0 
    ? round(($workout['completed_exercises'] / $workout['total_exercises']) * 100) 
    : 0;

$isCompleted = $workout['status'] === 'completed';
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h2">
                <i class="bi bi-play-circle text-primary"></i> 
                <?php echo htmlspecialchars($workout['name']); ?>
            </h1>
            <small class="text-muted">
                <i class="bi bi-calendar"></i> <?php echo date('d.m.Y H:i', strtotime($workout['created_at'])); ?>
            </small>
        </div>
        <div>
            <a href="/dashboard.php?page=workouts" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Прогресс -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Прогрес</span>
                        <span class="fw-bold text-primary"><?php echo $progress; ?>%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo $progress; ?>%; transition: width 0.5s;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted small">
                            <i class="bi bi-check-circle"></i> 
                            <?php echo $workout['completed_exercises']; ?>/<?php echo $workout['total_exercises']; ?> виконано
                        </span>
                        <span class="text-muted small">
                            <i class="bi bi-clock"></i> <?php echo $workout['total_duration_min']; ?> хв
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Статус</span>
                        <span class="badge bg-<?php echo $isCompleted ? 'success' : 'warning'; ?> fs-6 px-3 py-2">
                            <?php echo $isCompleted ? '✅ Завершено' : '⏳ В процесі'; ?>
                        </span>
                    </div>
                    <?php if (!$isCompleted && $progress > 0): ?>
                        <button class="btn btn-success w-100 mt-3" id="completeWorkoutBtn">
                            <i class="bi bi-check-circle"></i> Завершити тренування
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Список упражнений -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0">
                <i class="bi bi-list-check text-primary"></i> Вправи
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush" id="exerciseList">
                <?php foreach ($exercises as $index => $ex): ?>
                    <div class="list-group-item exercise-item <?php echo $ex['is_completed'] ? 'completed' : ''; ?>" 
                         data-id="<?php echo $ex['id']; ?>"
                         data-plan-id="<?php echo $planId; ?>"
                         style="cursor: pointer; transition: all 0.3s;">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="badge bg-secondary rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <?php echo $index + 1; ?>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="exercise-name"><?php echo htmlspecialchars($ex['name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-tag"></i> <?php echo $ex['muscle_group']; ?>
                                            <span class="mx-1">•</span>
                                            <i class="bi bi-arrow-repeat"></i> <?php echo $ex['sets']; ?>×<?php echo $ex['reps']; ?>
                                            <?php if ($ex['weight_kg']): ?>
                                                <span class="mx-1">•</span>
                                                <i class="bi bi-weight"></i> <?php echo $ex['weight_kg']; ?> кг
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo $ex['is_completed'] ? 'success' : 'secondary'; ?> rounded-pill">
                                            <?php echo $ex['is_completed'] ? '✅ Виконано' : '⏳ Очікує'; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($ex['description']): ?>
                                    <p class="small text-muted mt-1 mb-0"><?php echo htmlspecialchars($ex['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPlanId = <?php echo $planId; ?>;
    let completedCount = <?php echo $workout['completed_exercises']; ?>;
    let totalCount = <?php echo $workout['total_exercises']; ?>;
    
    // Обработка клика по упражнению
    document.querySelectorAll('.exercise-item').forEach(function(item) {
        item.addEventListener('click', function() {
            if (this.classList.contains('completed')) {
                // Если уже выполнено - пропускаем
                return;
            }
            
            const exerciseId = this.dataset.id;
            const planId = this.dataset.planId;
            
            // Отмечаем как выполненное
            this.classList.add('completed');
            this.querySelector('.badge').textContent = '✅ Виконано';
            this.querySelector('.badge').className = 'badge bg-success rounded-pill';
            
            // Обновляем счетчики
            completedCount++;
            updateProgress(completedCount, totalCount);
            
            // Отправляем на сервер
            const formData = new FormData();
            formData.append('action', 'complete');
            formData.append('exercise_id', exerciseId);
            formData.append('plan_id', planId);
            
            fetch('/api/workout-track.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showToast('Помилка збереження прогресу', 'danger');
                }
            })
            .catch(error => {
                showToast('Помилка з'єднання', 'danger');
            });
        });
    });
    
    // Обновление прогресса
    function updateProgress(completed, total) {
        const progress = Math.round((completed / total) * 100);
        const progressBar = document.querySelector('.progress-bar');
        const progressText = document.querySelector('.progress + .d-flex .text-muted:first-child');
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
        
        if (progressText) {
            progressText.innerHTML = `<i class="bi bi-check-circle"></i> ${completed}/${total} виконано`;
        }
        
        // Обновляем проценты в заголовке
        const percentText = document.querySelector('.progress + .d-flex .fw-bold');
        if (percentText) {
            percentText.textContent = progress + '%';
        }
    }
    
    // Завершение тренировки
    const completeBtn = document.getElementById('completeWorkoutBtn');
    if (completeBtn) {
        completeBtn.addEventListener('click', function() {
            if (completedCount < totalCount) {
                if (!confirm('Ви виконали не всі вправи. Завершити тренування?')) {
                    return;
                }
            }
            
            const formData = new FormData();
            formData.append('action', 'finish');
            formData.append('plan_id', currentPlanId);
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Завершення...';
            
            fetch('/api/workout-track.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('🎉 Тренування завершено!', 'success');
                    setTimeout(() => {
                        window.location.href = '/dashboard.php?page=workout-detail&id=' + currentPlanId;
                    }, 1500);
                } else {
                    showToast(data.error || 'Помилка завершення', 'danger');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-circle"></i> Завершити тренування';
                }
            })
            .catch(error => {
                showToast('Помилка з'єднання', 'danger');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-check-circle"></i> Завершити тренування';
            });
        });
    }
});
</script>

<style>
.exercise-item {
    transition: all 0.3s ease;
}

.exercise-item:hover {
    background-color: #f8f9fa;
}

.exercise-item.completed {
    background-color: #d1e7dd;
    border-color: #badbcc;
}

.exercise-item.completed .exercise-name {
    text-decoration: line-through;
    color: #198754;
}

.exercise-item .badge {
    transition: all 0.3s ease;
}
</style>