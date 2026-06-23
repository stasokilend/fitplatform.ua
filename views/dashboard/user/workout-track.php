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

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitPlatform - Тренування</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset('/assets/css/style.css'); ?>" rel="stylesheet">
    <style>
        /* ===== ТАЙМЕР ===== */
        .timer-fixed {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #1A1A2E 0%, #16213E 100%);
            border-top: 2px solid rgba(108, 99, 255, 0.3);
            box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.4);
            z-index: 1040;
            padding: 12px 0;
            backdrop-filter: blur(10px);
        }

        .timer-fixed .timer-display {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 2.8rem;
            color: #FFFFFF;
            letter-spacing: 2px;
            text-shadow: 0 0 20px rgba(108, 99, 255, 0.3);
            transition: color 0.3s ease;
            line-height: 1;
        }

        .timer-fixed .btn-timer {
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            border: none;
            min-width: 100px;
        }

        .timer-fixed .btn-timer:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .timer-fixed .btn-timer:active {
            transform: scale(0.95);
        }

        .timer-fixed .btn-timer-start {
            background: linear-gradient(135deg, #00D2A0 0%, #00A87E 100%);
            color: #fff;
        }

        .timer-fixed .btn-timer-start:hover {
            box-shadow: 0 4px 20px rgba(0, 210, 160, 0.4);
        }

        .timer-fixed .btn-timer-pause {
            background: linear-gradient(135deg, #FFB347 0%, #FF8C00 100%);
            color: #fff;
        }

        .timer-fixed .btn-timer-pause:hover {
            box-shadow: 0 4px 20px rgba(255, 179, 71, 0.4);
        }

        .timer-fixed .btn-timer-reset {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
            min-width: 80px;
        }

        .timer-fixed .btn-timer-reset:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .timer-fixed .timer-stats {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75rem;
        }

        .timer-fixed .timer-stats span {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
        }

        .timer-fixed .timer-status {
            font-size: 0.75rem;
            padding: 3px 12px;
            border-radius: 50px;
            display: inline-block;
        }

        .timer-fixed .timer-status.running {
            background: rgba(0, 210, 160, 0.2);
            color: #00D2A0;
        }

        .timer-fixed .timer-status.paused {
            background: rgba(255, 179, 71, 0.2);
            color: #FFB347;
        }

        .timer-fixed .timer-status.stopped {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
        }

        .timer-fixed .timer-display.running {
            animation: pulseTimer 2s ease-in-out infinite;
        }

        @keyframes pulseTimer {
            0%, 100% { text-shadow: 0 0 20px rgba(108, 99, 255, 0.3); }
            50% { text-shadow: 0 0 40px rgba(108, 99, 255, 0.6), 0 0 60px rgba(108, 99, 255, 0.2); }
        }

        /* ===== МОБИЛЬНАЯ ВЕРСИЯ ТАЙМЕРА (сверху) ===== */
        .timer-mobile {
            display: none;
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #1A1A2E 0%, #16213E 100%);
            border-bottom: 2px solid rgba(108, 99, 255, 0.3);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            z-index: 1040;
            padding: 8px 0;
            backdrop-filter: blur(10px);
        }

        .timer-mobile .timer-display {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 1.6rem;
            color: #FFFFFF;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(108, 99, 255, 0.3);
            line-height: 1;
        }

        .timer-mobile .btn-timer {
            border-radius: 50px;
            padding: 4px 12px;
            font-weight: 600;
            font-size: 0.65rem;
            transition: all 0.3s ease;
            border: none;
            min-width: 60px;
        }

        .timer-mobile .btn-timer-start {
            background: linear-gradient(135deg, #00D2A0 0%, #00A87E 100%);
            color: #fff;
        }

        .timer-mobile .btn-timer-pause {
            background: linear-gradient(135deg, #FFB347 0%, #FF8C00 100%);
            color: #fff;
        }

        .timer-mobile .btn-timer-reset {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
            min-width: 50px;
        }

        .timer-mobile .timer-status {
            font-size: 0.6rem;
            padding: 2px 10px;
            border-radius: 50px;
            display: inline-block;
        }

        .timer-mobile .timer-status.running {
            background: rgba(0, 210, 160, 0.2);
            color: #00D2A0;
        }

        .timer-mobile .timer-status.paused {
            background: rgba(255, 179, 71, 0.2);
            color: #FFB347;
        }

        .timer-mobile .timer-status.stopped {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
        }

        .timer-mobile .timer-display.running {
            animation: pulseTimer 2s ease-in-out infinite;
        }

        /* ===== ОБЩИЕ СТИЛИ ===== */
        .workout-content {
            padding-bottom: 100px;
        }

        .exercise-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .exercise-item:hover {
            background-color: #f8f9fa;
            border-left-color: #6C63FF;
        }

        .exercise-item.completed {
            background-color: #d1e7dd;
            border-left-color: #198754;
        }

        .exercise-item.completed .exercise-name {
            text-decoration: line-through;
            color: #198754;
        }

        .exercise-item .exercise-number {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* ===== АДАПТАЦИЯ ===== */
        @media (max-width: 768px) {
            .timer-fixed {
                display: none !important;
            }
            .timer-mobile {
                display: block !important;
            }
            .workout-content {
                padding-top: 10px;
                padding-bottom: 20px;
            }
        }

        @media (min-width: 769px) {
            .timer-fixed {
                display: block !important;
            }
            .timer-mobile {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- ===== МОБИЛЬНЫЙ ТАЙМЕР (СВЕРХУ) ===== -->
<div class="timer-mobile" id="timerMobile">
    <div class="container-fluid px-2">
        <div class="row align-items-center">
            <div class="col-4 text-start">
                <div class="timer-display" id="timerDisplayMobile">00:00:00</div>
                <span class="timer-status stopped" id="timerStatusMobile">⏸️</span>
            </div>
            <div class="col-8 text-end">
                <div class="d-flex gap-1 justify-content-end flex-wrap">
                    <button class="btn btn-timer btn-timer-start" id="timerMainBtnMobile">
                        <i class="bi bi-play-fill"></i>
                    </button>
                    <button class="btn btn-timer btn-timer-reset" id="timerResetBtnMobile">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== ОСНОВНОЙ КОНТЕНТ ===== -->
<div class="container-fluid workout-content">
    <!-- Заголовок -->
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

    <!-- Прогресс -->
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">Прогрес</span>
                    <span class="fw-bold text-primary" id="workoutProgressPercent"><?php echo $progress; ?>%</span>
                </div>
                <div class="progress" style="height: 12px;">
                    <div class="progress-bar bg-primary" id="workoutProgressBar" style="width: <?php echo $progress; ?>%; transition: width 0.5s;">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-muted small" id="workoutProgressText">
                        <i class="bi bi-check-circle"></i> 
                        <?php echo $workout['completed_exercises']; ?>/<?php echo $workout['total_exercises']; ?> виконано
                    </span>
                    <span class="text-muted small" id="workoutCaloriesDisplay">
                        <i class="bi bi-fire text-danger"></i> 
                        <span id="workoutCalories">0</span> ккал
                    </span>
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
                    <?php if (!$isCompleted): ?>
                        <button class="btn btn-success mt-3 <?php echo $progress > 0 ? '' : 'd-none'; ?>" id="completeWorkoutBtn">
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
                         data-exercise="<?php echo $ex['id']; ?>">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="badge bg-secondary rounded-circle exercise-number">
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
                                        <span class="badge bg-<?php echo $ex['is_completed'] ? 'success' : 'secondary'; ?> rounded-pill status-badge">
                                            <?php echo $ex['is_completed'] ? '✅ Виконано' : '⏳ Очікує'; ?>
                                        </span>
                                    </div>
                                </div>
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

<!-- ===== ДЕСКТОПНЫЙ ТАЙМЕР (ВНИЗУ) ===== -->
<div class="timer-fixed" id="timerCard">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 col-lg-3 text-center text-md-start">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <div class="timer-display" id="timerDisplay">00:00:00</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="timer-status stopped" id="timerStatus">⏸️ Не запущено</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-lg-6 text-center my-2 my-md-0">
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button class="btn btn-timer btn-timer-start" id="timerMainBtn">
                        <i class="bi bi-play-fill"></i> Старт
                    </button>
                    <button class="btn btn-timer btn-timer-reset" id="timerResetBtn">
                        <i class="bi bi-arrow-counterclockwise"></i> Скинути
                    </button>
                </div>
            </div>
            <div class="col-md-3 text-center text-md-end">
                <div class="timer-stats">
                    <div>Час тренування: <span id="workoutTime">00:00:00</span></div>
                    <div class="mt-1">
                        <i class="bi bi-check-circle text-success"></i> 
                        <span id="completedStats"><?php echo $workout['completed_exercises']; ?></span>/
                        <span id="totalStats"><?php echo $workout['total_exercises']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== ПЕРЕКЛЮЧЕНИЕ УПРАЖНЕНИЙ =====
    let completedCount = <?php echo $workout['completed_exercises']; ?>;
    let totalCount = <?php echo $workout['total_exercises']; ?>;
    let currentWorkoutId = <?php echo $workoutId; ?>;

    document.querySelectorAll('.exercise-item').forEach(function(item) {
        item.addEventListener('click', function() {
            const exerciseId = this.dataset.exercise;
            
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
                    this.classList.toggle('completed');
                    
                    // Обновляем только статус-бейдж (номер не трогаем)
                    const badge = this.querySelector('.status-badge');
                    if (badge) {
                        if (this.classList.contains('completed')) {
                            badge.textContent = '✅ Виконано';
                            badge.className = 'badge bg-success rounded-pill status-badge';
                            completedCount++;
                            playSound('complete');
                            showToast('✅ Вправу виконано!', 'success');
                        } else {
                            badge.textContent = '⏳ Очікує';
                            badge.className = 'badge bg-secondary rounded-pill status-badge';
                            completedCount--;
                            showToast('🔄 Виконання скасовано', 'info');
                        }
                    }
                    
                    updateProgress(completedCount, totalCount);
                    document.getElementById('completedStats').textContent = completedCount;
                }
            })
            .catch(function(error) {
                console.error('Ошибка:', error);
                showToast('Помилка з\'єднання', 'danger');
            });
        });
    });

    function updateProgress(completed, total) {
        const progress = total > 0 ? Math.round((completed / total) * 100) : 0;
        const progressBar = document.getElementById('workoutProgressBar');
        const progressText = document.getElementById('workoutProgressText');
        const percentText = document.getElementById('workoutProgressPercent');
        const completeBtn = document.getElementById('completeWorkoutBtn');
        
        if (progressBar) progressBar.style.width = progress + '%';
        if (progressText) {
            progressText.innerHTML = `<i class="bi bi-check-circle"></i> ${completed}/${total} виконано`;
        }
        if (percentText) percentText.textContent = progress + '%';
        if (completeBtn) {
            completeBtn.classList.toggle('d-none', completed <= 0);
        }
    }

    // ===== ЗВУК =====
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

    // ===== ТАЙМЕР (ОБЩАЯ ЛОГИКА ДЛЯ ДВУХ ВЕРСИЙ) =====
    let timerInterval = null;
    let isRunning = false;
    let isPaused = false;
    let seconds = 0;
    let workoutSeconds = 0;

    // Элементы десктопного таймера
    const timerDisplay = document.getElementById('timerDisplay');
    const timerStatus = document.getElementById('timerStatus');
    const timerMainBtn = document.getElementById('timerMainBtn');
    const timerResetBtn = document.getElementById('timerResetBtn');
    const workoutTimeDisplay = document.getElementById('workoutTime');

    // Элементы мобильного таймера
    const timerDisplayMobile = document.getElementById('timerDisplayMobile');
    const timerStatusMobile = document.getElementById('timerStatusMobile');
    const timerMainBtnMobile = document.getElementById('timerMainBtnMobile');
    const timerResetBtnMobile = document.getElementById('timerResetBtnMobile');

    function formatTime(totalSeconds) {
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    function updateDisplay() {
        const timeStr = formatTime(seconds);
        const workoutStr = formatTime(workoutSeconds);
        
        // Десктоп
        if (timerDisplay) timerDisplay.textContent = timeStr;
        if (workoutTimeDisplay) workoutTimeDisplay.textContent = workoutStr;
        
        // Мобильный
        if (timerDisplayMobile) timerDisplayMobile.textContent = timeStr;
    }

    function updateStatusUI(status, label) {
        // Десктоп
        if (timerStatus) {
            timerStatus.textContent = label;
            timerStatus.className = 'timer-status ' + status;
            if (status === 'running') {
                timerDisplay.classList.add('running');
            } else {
                timerDisplay.classList.remove('running');
            }
        }
        
        // Мобильный
        if (timerStatusMobile) {
            const mobileLabel = status === 'running' ? '▶️' : status === 'paused' ? '⏸️' : '⏸️';
            timerStatusMobile.textContent = mobileLabel;
            timerStatusMobile.className = 'timer-status ' + status;
            if (status === 'running') {
                timerDisplayMobile.classList.add('running');
            } else {
                timerDisplayMobile.classList.remove('running');
            }
        }
    }

    function updateMainButton(mode) {
        // Десктоп
        if (timerMainBtn) {
            if (mode === 'start') {
                timerMainBtn.innerHTML = '<i class="bi bi-play-fill"></i> Старт';
                timerMainBtn.className = 'btn btn-timer btn-timer-start';
            } else if (mode === 'pause') {
                timerMainBtn.innerHTML = '<i class="bi bi-pause-fill"></i> Пауза';
                timerMainBtn.className = 'btn btn-timer btn-timer-pause';
            } else if (mode === 'resume') {
                timerMainBtn.innerHTML = '<i class="bi bi-play-fill"></i> Продовжити';
                timerMainBtn.className = 'btn btn-timer btn-timer-start';
            }
        }
        
        // Мобильный
        if (timerMainBtnMobile) {
            if (mode === 'start') {
                timerMainBtnMobile.innerHTML = '<i class="bi bi-play-fill"></i>';
                timerMainBtnMobile.className = 'btn btn-timer btn-timer-start';
            } else if (mode === 'pause') {
                timerMainBtnMobile.innerHTML = '<i class="bi bi-pause-fill"></i>';
                timerMainBtnMobile.className = 'btn btn-timer btn-timer-pause';
            } else if (mode === 'resume') {
                timerMainBtnMobile.innerHTML = '<i class="bi bi-play-fill"></i>';
                timerMainBtnMobile.className = 'btn btn-timer btn-timer-start';
            }
        }
    }

    function startTimer() {
        if (isRunning && !isPaused) {
            pauseTimer();
            return;
        }
        
        if (isPaused) {
            isPaused = false;
            updateStatusUI('running', '▶️ В процесі');
            updateMainButton('pause');
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            timerInterval = setInterval(function() {
                seconds++;
                workoutSeconds++;
                updateDisplay();
            }, 1000);
            return;
        }
        
        isRunning = true;
        updateStatusUI('running', '▶️ В процесі');
        updateMainButton('pause');
        
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        timerInterval = setInterval(function() {
            seconds++;
            workoutSeconds++;
            updateDisplay();
        }, 1000);
    }

    function pauseTimer() {
        if (!isRunning || isPaused) return;
        
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        
        isPaused = true;
        updateStatusUI('paused', '⏸️ На паузі');
        updateMainButton('resume');
    }

    function resetTimer() {
        if (!confirm('Скинути таймер?')) return;
        
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        
        isRunning = false;
        isPaused = false;
        seconds = 0;
        workoutSeconds = 0;
        
        updateStatusUI('stopped', '⏸️ Не запущено');
        updateMainButton('start');
        updateDisplay();
        showToast('Таймер скинуто', 'info');
    }

    // Обработчики для десктопной версии
    if (timerMainBtn) timerMainBtn.addEventListener('click', startTimer);
    if (timerResetBtn) timerResetBtn.addEventListener('click', resetTimer);

    // Обработчики для мобильной версии
    if (timerMainBtnMobile) timerMainBtnMobile.addEventListener('click', startTimer);
    if (timerResetBtnMobile) timerResetBtnMobile.addEventListener('click', resetTimer);

    // ===== ЗАВЕРШЕНИЕ ТРЕНИРОВКИ =====
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
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
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
            showToast('Тренування могло бути завершено. Перевіряємо статус...', 'warning');
            setTimeout(() => {
                window.location.href = '/dashboard.php?page=workout-detail&id=' + currentWorkoutId;
            }, 900);
        });
    });

    // ===== ЗАМЕТКИ =====
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

    // ===== ИНИЦИАЛИЗАЦИЯ =====
    updateDisplay();
    updateMainButton('start');

    // Обновление калорий при завершении упражнения
function updateCalories() {
    const formData = new FormData();
    formData.append('action', 'calculate_calories');
    formData.append('workout_id', currentWorkoutId);
    
    fetch('/api/workout.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('workoutCalories').textContent = data.calories;
        }
    })
    .catch(error => console.error('Ошибка:', error));
}

// Вызываем при каждом переключении упражнения
document.querySelectorAll('.exercise-item').forEach(function(item) {
    item.addEventListener('click', function() {
        // ... существующий код ...
        updateCalories();
    });
});
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>