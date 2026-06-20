<?php
require_once 'config/database.php';
require_once 'controllers/WorkoutController.php';

$userId = $_SESSION['user_id'];
$workoutId = (int)($_GET['id'] ?? 0);

if (!$workoutId) {
    header('Location: /dashboard.php?page=workouts');
    exit;
}

$workoutCtrl = new WorkoutController($userId);
$workout = $workoutCtrl->getWorkout($workoutId);

if (!$workout) {
    header('Location: /dashboard.php?page=workouts');
    exit;
}

$exercises = $workoutCtrl->getWorkoutExercises($workoutId);
$notes = $workoutCtrl->getNotes($workoutId);

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
                <span class="mx-2">•</span>
                <i class="bi bi-clock"></i> <?php echo $workout['total_duration_min']; ?> хв
            </small>
        </div>
        <div>
            <a href="/dashboard.php?page=workouts" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Прогресс и статус -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Прогрес</span>
                        <span class="fw-bold text-primary"><?php echo $progress; ?>%</span>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo $progress; ?>%; transition: width 0.5s;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted small">
                            <i class="bi bi-check-circle"></i> 
                            <?php echo $workout['completed_exercises']; ?>/<?php echo $workout['total_exercises']; ?> виконано
                        </span>
                        <span class="text-muted small">
                            <i class="bi bi-fire"></i> <?php echo $workout['calories_burned'] ?? 0; ?> ккал
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Статус</span>
                        <span class="badge bg-<?php echo $isCompleted ? 'success' : 'warning'; ?> fs-6 px-3 py-2">
                            <?php echo $isCompleted ? '✅ Завершено' : '⏳ В процесі'; ?>
                        </span>
                    </div>
                    <?php if (!$isCompleted && $progress > 0): ?>
                        <button class="btn btn-success mt-3" id="completeWorkoutBtn">
                            <i class="bi bi-check-circle"></i> Завершити тренування
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Упражнения -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-check text-primary"></i> Вправи
            </h5>
            <span class="badge bg-secondary"><?php echo count($exercises); ?> вправ</span>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush" id="exerciseList">
                <?php foreach ($exercises as $index => $ex): ?>
                    <div class="list-group-item exercise-item <?php echo $ex['is_completed'] ? 'completed' : ''; ?>" 
                        data-exercise="<?php echo $ex['id']; ?>"
                        style="cursor: pointer; transition: all 0.3s;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <?php echo $index + 1; ?>
                                </span>
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
                            </div>
                            <div class="exercise-actions d-flex align-items-center">
                                <span class="badge bg-<?php echo $ex['is_completed'] ? 'success' : 'secondary'; ?> rounded-pill">
                                    <?php echo $ex['is_completed'] ? '✅ Виконано' : '⏳ Очікує'; ?>
                                </span>
                                <?php if ($ex['is_completed']): ?>
                                    <button class="btn btn-sm btn-outline-danger cancel-exercise-btn ms-2" 
                                            style="display: inline-block;"
                                            title="Скасувати виконання">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-danger cancel-exercise-btn ms-2" 
                                            style="display: none;"
                                            title="Скасувати виконання">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Заметки -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0">
                <i class="bi bi-pencil text-primary"></i> Нотатки
            </h5>
        </div>
        <div class="card-body">
            <?php if (count($notes) > 0): ?>
                <?php foreach ($notes as $note): ?>
                    <div class="border-bottom py-2">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($note['note'])); ?></p>
                        <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($note['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <form class="d-flex gap-2 mt-3" id="noteForm">
                <input type="hidden" name="workout_id" value="<?php echo $workoutId; ?>">
                <input type="text" name="note" class="form-control" placeholder="Додати нотатку..." <?php echo $isCompleted ? 'disabled' : ''; ?>>
                <button type="submit" class="btn btn-outline-primary" <?php echo $isCompleted ? 'disabled' : ''; ?>>
                    <i class="bi bi-send"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Добавить после прогресса, перед упражнениями -->
<!-- Таймер тренировки -->
<div class="card border-0 shadow-sm mb-4" id="timerCard">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4 text-center">
                <div class="display-4 fw-bold" id="timerDisplay" style="font-size: 3rem; font-variant-numeric: tabular-nums;">
                    00:00:00
                </div>
                <small class="text-muted" id="timerStatus">⏸️ На паузі</small>
            </div>
            <div class="col-md-8">
                <div class="d-flex gap-2 justify-content-center justify-content-md-end flex-wrap">
                    <button class="btn btn-success btn-gradient" id="timerStartBtn">
                        <i class="bi bi-play-fill"></i> Старт
                    </button>
                    <button class="btn btn-warning" id="timerPauseBtn" disabled>
                        <i class="bi bi-pause-fill"></i> Пауза
                    </button>
                    <button class="btn btn-danger" id="timerResetBtn">
                        <i class="bi bi-arrow-counterclockwise"></i> Скинути
                    </button>
                    <button class="btn btn-info text-white" id="timerRestBtn" disabled>
                        <i class="bi bi-clock"></i> Перерва (1 хв)
                    </button>
                </div>
                <div class="mt-2 text-center text-muted small" id="timerInfo">
                    Час тренування: <span id="workoutTime">00:00:00</span>
                    <span class="mx-2">|</span>
                    Перерв: <span id="restCount">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentWorkoutId = <?php echo $workoutId; ?>;
    let completedCount = <?php echo $workout['completed_exercises']; ?>;
    let totalCount = <?php echo $workout['total_exercises']; ?>;
    
    // ===== УПРАЖНЕНИЯ С ВОЗМОЖНОСТЬЮ ОТМЕНЫ =====
document.querySelectorAll('.exercise-item').forEach(function(item) {
    item.addEventListener('click', function(e) {
        // Если кликнули по кнопке удаления - пропускаем
        if (e.target.closest('.cancel-exercise-btn')) return;
        
        const exerciseId = this.dataset.exercise;
        const isCompleted = this.classList.contains('completed');
        
        // Если упражнение уже выполнено и не нажата кнопка отмены - показываем предупреждение
        if (isCompleted) {
            // Показываем кнопку отмены
            const cancelBtn = this.querySelector('.cancel-exercise-btn');
            if (cancelBtn) {
                cancelBtn.style.display = 'inline-block';
                setTimeout(() => {
                    cancelBtn.style.display = 'none';
                }, 3000);
            } else {
                // Создаем кнопку отмены
                const actions = this.querySelector('.exercise-actions');
                if (actions) {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-sm btn-outline-danger cancel-exercise-btn ms-2';
                    btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
                    btn.title = 'Скасувати виконання';
                    btn.style.display = 'inline-block';
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        cancelExercise(exerciseId, item);
                    });
                    actions.appendChild(btn);
                }
            }
            return;
        }
        
        // Отмечаем как выполненное
        markExerciseComplete(exerciseId, item);
            });
        });

        function markExerciseComplete(exerciseId, element) {
            const formData = new FormData();
            formData.append('action', 'toggle_exercise');
            formData.append('exercise_id', exerciseId);
            
            fetch('/api/workout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    element.classList.add('completed');
                    const badge = element.querySelector('.badge');
                    if (badge) {
                        badge.textContent = '✅ Виконано';
                        badge.className = 'badge bg-success rounded-pill';
                    }
                    
                    // Добавляем кнопку отмены
                    const actions = element.querySelector('.exercise-actions') || element.querySelector('.d-flex');
                    if (actions) {
                        const existingBtn = actions.querySelector('.cancel-exercise-btn');
                        if (!existingBtn) {
                            const btn = document.createElement('button');
                            btn.className = 'btn btn-sm btn-outline-danger cancel-exercise-btn ms-2';
                            btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
                            btn.title = 'Скасувати виконання';
                            btn.style.display = 'inline-block';
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                cancelExercise(exerciseId, element);
                            });
                            actions.appendChild(btn);
                        }
                    }
                    
                    completedCount++;
                    updateProgress(completedCount, totalCount);
                    playSound('complete');
                    showToast('✅ Вправу виконано!', 'success');
                }
            });
        }

        function cancelExercise(exerciseId, element) {
            if (!confirm('Скасувати виконання цієї вправи?')) return;
            
            const formData = new FormData();
            formData.append('action', 'toggle_exercise');
            formData.append('exercise_id', exerciseId);
            
            fetch('/api/workout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    element.classList.remove('completed');
                    const badge = element.querySelector('.badge');
                    if (badge) {
                        badge.textContent = '⏳ Очікує';
                        badge.className = 'badge bg-secondary rounded-pill';
                    }
                    
                    const cancelBtn = element.querySelector('.cancel-exercise-btn');
                    if (cancelBtn) {
                        cancelBtn.style.display = 'none';
                    }
                    
                    completedCount--;
                    updateProgress(completedCount, totalCount);
                    showToast('🔄 Виконання скасовано', 'info');
                }
            });
        }
    
    function updateProgress(completed, total) {
        const progress = Math.round((completed / total) * 100);
        const progressBar = document.querySelector('.progress .progress-bar');
        const progressText = document.querySelector('.progress + .d-flex .text-muted:first-child');
        
        if (progressBar) progressBar.style.width = progress + '%';
        if (progressText) {
            progressText.innerHTML = `<i class="bi bi-check-circle"></i> ${completed}/${total} виконано`;
        }
        
        const percentText = document.querySelector('.progress + .d-flex .fw-bold');
        if (percentText) percentText.textContent = progress + '%';
    }
    
    // Завершение тренировки
    document.getElementById('completeWorkoutBtn')?.addEventListener('click', function() {
        if (completedCount < totalCount) {
            if (!confirm('Ви виконали не всі вправи. Завершити тренування?')) return;
        }
        
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Завершення...';
        
        const formData = new FormData();
        formData.append('action', 'complete');
        formData.append('workout_id', currentWorkoutId);
        formData.append('calories', <?php echo $workout['calories_burned'] ?? 0; ?>);
        
        fetch('/api/workout.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('🎉 Тренування завершено!', 'success');
                setTimeout(() => {
                    window.location.href = '/dashboard.php?page=workout-detail&id=' + currentWorkoutId;
                }, 1500);
            } else {
                showToast(data.error || 'Помилка завершення', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Завершити тренування';
            }
        })
        .catch(function() {
            showToast('Помилка з\'єднання', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Завершити тренування';
        });
    });
    
    // Добавление заметки
    document.getElementById('noteForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'add_note');
        
        fetch('/api/workout.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Нотатку додано!', 'success');
                location.reload();
            } else {
                showToast(data.error || 'Помилка', 'danger');
            }
        })
        .catch(function() {
            showToast('Помилка з\'єднання', 'danger');
        });
    });
    
    // Звук
    function playSound(type) {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            if (type === 'complete') {
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            }
        } catch(e) {}
    }
});

// ===== ТАЙМЕР ТРЕНИРОВКИ =====
let timerInterval = null;
let isRunning = false;
let isPaused = false;
let isResting = false;
let seconds = 0;
let restCount = 0;
let workoutSeconds = 0;

const timerDisplay = document.getElementById('timerDisplay');
const timerStatus = document.getElementById('timerStatus');
const timerStartBtn = document.getElementById('timerStartBtn');
const timerPauseBtn = document.getElementById('timerPauseBtn');
const timerResetBtn = document.getElementById('timerResetBtn');
const timerRestBtn = document.getElementById('timerRestBtn');
const workoutTimeDisplay = document.getElementById('workoutTime');
const restCountDisplay = document.getElementById('restCount');

function formatTime(totalSeconds) {
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

function updateDisplay() {
    timerDisplay.textContent = formatTime(seconds);
    workoutTimeDisplay.textContent = formatTime(workoutSeconds);
    restCountDisplay.textContent = restCount;
}

function startTimer() {
    if (isRunning && !isPaused) return;
    
    if (isPaused) {
        // Продолжаем
        isPaused = false;
        timerStatus.textContent = '▶️ В процесі';
        timerStartBtn.innerHTML = '<i class="bi bi-play-fill"></i> Продовжити';
    } else if (isResting) {
        // Завершаем перерыв
        isResting = false;
        timerStatus.textContent = '▶️ В процесі';
        timerStartBtn.innerHTML = '<i class="bi bi-play-fill"></i> Продовжити';
        timerRestBtn.disabled = false;
    } else {
        // Запускаем с нуля
        isRunning = true;
        timerStatus.textContent = '▶️ В процесі';
        timerStartBtn.innerHTML = '<i class="bi bi-pause-fill"></i> Пауза';
        timerStartBtn.className = 'btn btn-warning';
    }
    
    timerStartBtn.disabled = true;
    timerPauseBtn.disabled = false;
    timerRestBtn.disabled = false;
    timerStartBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ...';
    
    if (!timerInterval) {
        timerInterval = setInterval(function() {
            seconds++;
            if (!isResting && !isPaused) {
                workoutSeconds++;
            }
            updateDisplay();
        }, 1000);
    }
    
    setTimeout(() => {
        timerStartBtn.disabled = false;
        timerStartBtn.innerHTML = '<i class="bi bi-play-fill"></i> Продовжити';
        timerStartBtn.className = 'btn btn-success btn-gradient';
    }, 500);
}

function pauseTimer() {
    if (!isRunning || isPaused) return;
    if (isResting) {
        // Если на перерыве - просто останавливаем отсчет
        isPaused = true;
        timerStatus.textContent = '⏸️ Перерва на паузі';
        timerStartBtn.innerHTML = '<i class="bi bi-play-fill"></i> Продовжити';
        timerStartBtn.disabled = false;
        timerPauseBtn.disabled = true;
        return;
    }
    
    isPaused = true;
    timerStatus.textContent = '⏸️ На паузі';
    timerStartBtn.innerHTML = '<i class="bi bi-play-fill"></i> Продовжити';
    timerStartBtn.disabled = false;
    timerPauseBtn.disabled = true;
}

function resetTimer() {
    if (!confirm('Скинути таймер?')) return;
    
    clearInterval(timerInterval);
    timerInterval = null;
    isRunning = false;
    isPaused = false;
    isResting = false;
    seconds = 0;
    workoutSeconds = 0;
    restCount = 0;
    
    timerStatus.textContent = '⏸️ На паузі';
    timerStartBtn.innerHTML = '<i class="bi bi-play-fill"></i> Старт';
    timerStartBtn.className = 'btn btn-success btn-gradient';
    timerStartBtn.disabled = false;
    timerPauseBtn.disabled = true;
    timerRestBtn.disabled = true;
    updateDisplay();
    showToast('Таймер скинуто', 'info');
}

function startRest() {
    if (isResting) return;
    if (!isRunning) {
        showToast('Спочатку запустіть таймер', 'warning');
        return;
    }
    
    isResting = true;
    isPaused = false;
    restCount++;
    timerStatus.textContent = '☕ Перерва (1 хв)';
    timerStartBtn.innerHTML = '<i class="bi bi-clock"></i> Завершити перерву';
    timerStartBtn.className = 'btn btn-info text-white';
    timerStartBtn.disabled = false;
    timerPauseBtn.disabled = true;
    timerRestBtn.disabled = true;
    
    // Таймер обратного отсчета
    let restSeconds = 60;
    timerDisplay.textContent = formatTime(restSeconds);
    
    const restInterval = setInterval(function() {
        restSeconds--;
        timerDisplay.textContent = formatTime(restSeconds);
        
        if (restSeconds <= 10) {
            timerDisplay.style.color = '#dc3545';
            if (restSeconds <= 5) {
                timerDisplay.style.fontWeight = '900';
            }
        }
        
        if (restSeconds <= 0) {
            clearInterval(restInterval);
            timerDisplay.style.color = '';
            timerDisplay.style.fontWeight = '';
            timerStatus.textContent = '▶️ В процесі';
            timerStartBtn.innerHTML = '<i class="bi bi-play-fill"></i> Продовжити';
            timerStartBtn.className = 'btn btn-success btn-gradient';
            timerRestBtn.disabled = false;
            isResting = false;
            isPaused = false;
            timerDisplay.textContent = formatTime(seconds);
            showToast('⏰ Перерва закінчилась!', 'info');
        }
    }, 1000);
    
    // Сохраняем интервал для очистки при ручном завершении
    window._restInterval = restInterval;
}

// Обработчики кнопок
timerStartBtn.addEventListener('click', function() {
    if (isResting) {
        // Завершаем перерыв
        if (window._restInterval) {
            clearInterval(window._restInterval);
            window._restInterval = null;
        }
        timerDisplay.style.color = '';
        timerDisplay.style.fontWeight = '';
        isResting = false;
        isPaused = false;
        timerStatus.textContent = '▶️ В процесі';
        timerStartBtn.innerHTML = '<i class="bi bi-play-fill"></i> Продовжити';
        timerStartBtn.className = 'btn btn-success btn-gradient';
        timerPauseBtn.disabled = false;
        timerRestBtn.disabled = false;
        timerDisplay.textContent = formatTime(seconds);
        showToast('Перерву завершено', 'info');
        return;
    }
    
    if (!isRunning) {
        startTimer();
    } else if (isPaused) {
        // Продолжаем
        isPaused = false;
        timerStatus.textContent = '▶️ В процесі';
        timerStartBtn.innerHTML = '<i class="bi bi-play-fill"></i> Продовжити';
        timerPauseBtn.disabled = false;
    }
});

timerPauseBtn.addEventListener('click', pauseTimer);

timerResetBtn.addEventListener('click', resetTimer);

timerRestBtn.addEventListener('click', startRest);

// Инициализация
updateDisplay();
timerPauseBtn.disabled = true;
timerRestBtn.disabled = true;
</script>