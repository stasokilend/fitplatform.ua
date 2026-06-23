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
        /* ===== ТАЙМЕР СТИКИ ===== */
        .timer-sticky-wrapper {
            position: sticky;
            top: 0;
            z-index: 1040;
            padding-top: 8px;
            padding-bottom: 8px;
            background: linear-gradient(180deg, #f0f2f5 0%, rgba(240, 242, 245, 0.95) 100%);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            margin: 0 -12px;
            padding-left: 12px;
            padding-right: 12px;
            transition: box-shadow 0.3s ease, padding 0.3s ease;
        }

        .timer-sticky-wrapper.is-sticky {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .timer-top {
            background: linear-gradient(135deg, #1A1A2E 0%, #16213E 100%);
            border-radius: 16px;
            border: 1px solid rgba(108, 99, 255, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            padding: 16px 24px;
            transition: all 0.3s ease;
        }

        .timer-sticky-wrapper.is-sticky .timer-top {
            padding: 10px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        .timer-top .timer-display {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 3.2rem;
            color: #FFFFFF;
            letter-spacing: 3px;
            text-shadow: 0 0 30px rgba(108, 99, 255, 0.3);
            transition: all 0.3s ease;
            line-height: 1;
        }

        .timer-sticky-wrapper.is-sticky .timer-top .timer-display {
            font-size: 2.4rem;
            letter-spacing: 2px;
        }

        .timer-top .timer-display.running {
            animation: pulseTimer 2s ease-in-out infinite;
        }

        @keyframes pulseTimer {
            0%, 100% { text-shadow: 0 0 20px rgba(108, 99, 255, 0.3); }
            50% { text-shadow: 0 0 40px rgba(108, 99, 255, 0.6), 0 0 60px rgba(108, 99, 255, 0.2); }
        }

        .timer-top .timer-status {
            font-size: 0.8rem;
            padding: 4px 14px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .timer-sticky-wrapper.is-sticky .timer-top .timer-status {
            font-size: 0.7rem;
            padding: 2px 10px;
        }

        .timer-top .timer-status.running {
            background: rgba(0, 210, 160, 0.2);
            color: #00D2A0;
        }

        .timer-top .timer-status.paused {
            background: rgba(255, 179, 71, 0.2);
            color: #FFB347;
        }

        .timer-top .timer-status.stopped {
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.5);
        }

        .timer-top .btn-timer {
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            min-width: 100px;
        }

        .timer-sticky-wrapper.is-sticky .timer-top .btn-timer {
            padding: 6px 16px;
            font-size: 0.8rem;
            min-width: 70px;
        }

        .timer-top .btn-timer:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .timer-top .btn-timer:active {
            transform: scale(0.95);
        }

        .timer-top .btn-timer-start {
            background: linear-gradient(135deg, #00D2A0 0%, #00A87E 100%);
            color: #fff;
        }

        .timer-top .btn-timer-start:hover {
            box-shadow: 0 4px 20px rgba(0, 210, 160, 0.4);
        }

        .timer-top .btn-timer-pause {
            background: linear-gradient(135deg, #FFB347 0%, #FF8C00 100%);
            color: #fff;
        }

        .timer-top .btn-timer-pause:hover {
            box-shadow: 0 4px 20px rgba(255, 179, 71, 0.4);
        }

        .timer-top .btn-timer-reset {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
            min-width: 80px;
        }

        .timer-top .btn-timer-reset:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .timer-top .timer-stats {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .timer-sticky-wrapper.is-sticky .timer-top .timer-stats {
            font-size: 0.7rem;
        }

        .timer-top .timer-stats span {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
        }

        .timer-top .timer-workout-time {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .timer-sticky-wrapper.is-sticky .timer-top .timer-workout-time {
            font-size: 0.75rem;
        }

        .timer-top .timer-workout-time span {
            color: #FFFFFF;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .timer-sticky-wrapper.is-sticky .timer-top .timer-workout-time span {
            font-size: 0.95rem;
        }

        /* ===== ПРОГРЕСС-БАР В ТАЙМЕРЕ ===== */
        .timer-progress-bar {
            height: 3px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            margin-top: 8px;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .timer-sticky-wrapper.is-sticky .timer-progress-bar {
            opacity: 1;
        }

        .timer-progress-bar .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #6C63FF, #FF6584);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        /* ===== ОБЩИЕ СТИЛИ ===== */
        .workout-content {
            padding-bottom: 20px;
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

        /* ===== БЛОК ЗАМЕТОК ===== */
        .notes-sidebar {
            position: sticky;
            top: 120px;
        }

        .notes-sidebar .note-item {
            padding: 12px 16px;
            border-radius: 12px;
            background: #f8f9fa;
            border-left: 4px solid #6C63FF;
            transition: all 0.3s ease;
        }

        .notes-sidebar .note-item:hover {
            background: #f0f2ff;
            transform: translateX(4px);
        }

        .notes-sidebar .note-item .note-text {
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 4px;
        }

        .notes-sidebar .note-item .note-date {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .notes-sidebar .note-item .note-delete {
            opacity: 0;
            transition: all 0.3s ease;
            color: #dc3545;
            cursor: pointer;
            padding: 2px 6px;
        }

        .notes-sidebar .note-item:hover .note-delete {
            opacity: 1;
        }

        .notes-sidebar .note-item .note-delete:hover {
            background: rgba(220, 53, 69, 0.1);
            border-radius: 4px;
        }

        .notes-sidebar .note-input-group {
            display: flex;
            gap: 8px;
        }

        .notes-sidebar .note-input-group .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .notes-sidebar .note-input-group .form-control:focus {
            border-color: #6C63FF;
            box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.12);
        }

        .notes-sidebar .note-input-group .btn {
            border-radius: 12px;
            padding: 0 16px;
        }

        .notes-sidebar .empty-notes {
            text-align: center;
            padding: 30px 20px;
            color: #6c757d;
        }

        .notes-sidebar .empty-notes i {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 12px;
            opacity: 0.3;
        }

        /* ===== АДАПТАЦИЯ ===== */
        @media (max-width: 992px) {
            .timer-sticky-wrapper {
                margin: 0 -8px;
                padding-left: 8px;
                padding-right: 8px;
            }

            .notes-sidebar {
                top: 100px;
            }
        }

        @media (max-width: 768px) {
            .timer-sticky-wrapper {
                padding-top: 4px;
                padding-bottom: 4px;
                margin: 0 -4px;
                padding-left: 4px;
                padding-right: 4px;
            }

            .timer-top {
                padding: 12px 16px;
                border-radius: 12px;
            }

            .timer-sticky-wrapper.is-sticky .timer-top {
                padding: 8px 12px;
                border-radius: 10px;
            }

            .timer-top .timer-display {
                font-size: 2rem;
                letter-spacing: 1px;
            }

            .timer-sticky-wrapper.is-sticky .timer-top .timer-display {
                font-size: 1.6rem;
            }

            .timer-top .btn-timer {
                padding: 6px 14px;
                font-size: 0.75rem;
                min-width: 60px;
            }

            .timer-sticky-wrapper.is-sticky .timer-top .btn-timer {
                padding: 4px 10px;
                font-size: 0.65rem;
                min-width: 50px;
            }

            .timer-top .timer-stats {
                font-size: 0.7rem;
            }

            .timer-sticky-wrapper.is-sticky .timer-top .timer-stats {
                font-size: 0.6rem;
            }

            .timer-top .timer-workout-time {
                font-size: 0.7rem;
            }

            .timer-sticky-wrapper.is-sticky .timer-top .timer-workout-time {
                font-size: 0.6rem;
            }

            .timer-top .timer-workout-time span {
                font-size: 0.9rem;
            }

            .timer-sticky-wrapper.is-sticky .timer-top .timer-workout-time span {
                font-size: 0.8rem;
            }

            .timer-progress-bar {
                height: 2px;
                margin-top: 4px;
            }

            .notes-sidebar {
                position: relative;
                top: 0;
                margin-top: 20px;
            }
        }

        @media (max-width: 576px) {
            .timer-top .timer-display {
                font-size: 1.6rem;
            }

            .timer-sticky-wrapper.is-sticky .timer-top .timer-display {
                font-size: 1.3rem;
            }

            .timer-top {
                padding: 10px 12px;
            }

            .timer-top .btn-timer {
                padding: 4px 10px;
                font-size: 0.7rem;
                min-width: 50px;
            }

            .timer-sticky-wrapper.is-sticky .timer-top .btn-timer {
                padding: 3px 8px;
                font-size: 0.6rem;
                min-width: 40px;
            }
        }

        /* ===== АНИМАЦИИ ===== */
        .note-item.fade-in {
            animation: fadeInNote 0.4s ease;
        }

        @keyframes fadeInNote {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ===== СКРЫВАЕМ ПРИ ПЕЧАТИ ===== */
        @media print {
            .timer-sticky-wrapper {
                position: relative !important;
                box-shadow: none !important;
            }
            .timer-progress-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- ===== ОСНОВНОЙ КОНТЕНТ ===== -->
<div class="container-fluid workout-content">

    <!-- ===== ТАЙМЕР СТИКИ ===== -->
    <div class="timer-sticky-wrapper" id="timerStickyWrapper">
        <div class="timer-top" id="timerTop">
            <div class="row align-items-center">
                <!-- Время -->
                <div class="col-md-3 col-lg-3 text-center text-md-start mb-2 mb-md-0">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <div class="timer-display" id="timerDisplay">00:00:00</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="timer-status stopped" id="timerStatus">⏸️ Не запущено</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Кнопки -->
                <div class="col-md-5 col-lg-5 text-center mb-2 mb-md-0">
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <button class="btn btn-timer btn-timer-start" id="timerMainBtn">
                            <i class="bi bi-play-fill"></i> Старт
                        </button>
                        <button class="btn btn-timer btn-timer-reset" id="timerResetBtn">
                            <i class="bi bi-arrow-counterclockwise"></i> Скинути
                        </button>
                    </div>
                </div>
                
                <!-- Статистика -->
                <div class="col-md-4 col-lg-4 text-center text-md-end">
                    <div class="timer-stats">
                        <div class="timer-workout-time">
                            ⏱️ Час тренування: <span id="workoutTime">00:00:00</span>
                        </div>
                        <div class="mt-1">
                            <i class="bi bi-check-circle text-success"></i> 
                            <span id="completedStats"><?php echo $workout['completed_exercises']; ?></span>/
                            <span id="totalStats"><?php echo $workout['total_exercises']; ?></span>
                            вправ виконано
                        </div>
                    </div>
                </div>
            </div>
            <!-- Прогресс-бар (появляется при скролле) -->
            <div class="timer-progress-bar">
                <div class="progress-fill" id="timerProgressFill"></div>
            </div>
        </div>
    </div>

    <!-- Заголовок -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom" style="margin-top: 16px;">
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

    <!-- ===== ОСНОВНАЯ СЕТКА: ЛЕВАЯ ЧАСТЬ (прогресс + упражнения) + ПРАВАЯ ЧАСТЬ (заметки) ===== -->
    <div class="row g-4">
        <!-- ЛЕВАЯ КОЛОНКА -->
        <div class="col-lg-8">
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
        </div>

        <!-- ПРАВАЯ КОЛОНКА - ЗАМЕТКИ -->
        <div class="col-lg-4">
            <div class="notes-sidebar" id="notesSidebar">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil text-primary"></i> Нотатки
                            <span class="badge bg-secondary float-end"><?php echo count($notes); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Список заметок -->
                        <div id="notesContainer">
                            <?php if (count($notes) > 0): ?>
                                <?php foreach ($notes as $note): ?>
                                    <div class="note-item mb-2" data-note-id="<?php echo $note['id']; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="note-text"><?php echo nl2br(htmlspecialchars($note['note'])); ?></div>
                                                <div class="note-date">
                                                    <i class="bi bi-clock"></i> <?php echo date('d.m.Y H:i', strtotime($note['created_at'])); ?>
                                                </div>
                                            </div>
                                            <span class="note-delete" onclick="deleteNote(<?php echo $note['id']; ?>)" title="Видалити">
                                                <i class="bi bi-x-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-notes">
                                    <i class="bi bi-journal-text"></i>
                                    <p class="mb-0">Немає нотаток</p>
                                    <small class="text-muted">Додайте першу нотатку нижче</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <!-- Форма добавления заметки -->
                        <form id="noteForm" class="mt-2">
                            <input type="hidden" name="workout_id" value="<?php echo $workoutId; ?>">
                            <div class="note-input-group">
                                <input type="text" name="note" class="form-control" placeholder="Додати нотатку..." <?php echo $isCompleted ? 'disabled' : ''; ?>>
                                <button type="submit" class="btn btn-primary" <?php echo $isCompleted ? 'disabled' : ''; ?>>
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== СТИКИ ТАЙМЕР =====
    const timerWrapper = document.getElementById('timerStickyWrapper');
    const timerTop = document.getElementById('timerTop');
    const timerProgressFill = document.getElementById('timerProgressFill');
    let initialOffset = 0;

    // Функция для определения позиции при скролле
    function handleScroll() {
        if (!timerWrapper) return;
        
        const rect = timerWrapper.getBoundingClientRect();
        const isSticky = rect.top <= 0;
        
        if (isSticky) {
            timerWrapper.classList.add('is-sticky');
        } else {
            timerWrapper.classList.remove('is-sticky');
        }
        
        // Обновляем прогресс-бар таймера
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollProgress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        timerProgressFill.style.width = Math.min(scrollProgress, 100) + '%';
    }

    // Добавляем обработчик скролла с throttle для производительности
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                handleScroll();
                ticking = false;
            });
            ticking = true;
        }
    });

    // Вызываем при загрузке
    handleScroll();

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
                    updateCalories();
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

    // ===== ТАЙМЕР =====
    let timerInterval = null;
    let isRunning = false;
    let isPaused = false;
    let seconds = 0;
    let workoutSeconds = 0;

    const timerDisplay = document.getElementById('timerDisplay');
    const timerStatus = document.getElementById('timerStatus');
    const timerMainBtn = document.getElementById('timerMainBtn');
    const timerResetBtn = document.getElementById('timerResetBtn');
    const workoutTimeDisplay = document.getElementById('workoutTime');

    function formatTime(totalSeconds) {
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    function updateDisplay() {
        const timeStr = formatTime(seconds);
        const workoutStr = formatTime(workoutSeconds);
        
        if (timerDisplay) timerDisplay.textContent = timeStr;
        if (workoutTimeDisplay) workoutTimeDisplay.textContent = workoutStr;
    }

    function updateStatusUI(status, label) {
        if (timerStatus) {
            timerStatus.textContent = label;
            timerStatus.className = 'timer-status ' + status;
            if (status === 'running') {
                timerDisplay.classList.add('running');
            } else {
                timerDisplay.classList.remove('running');
            }
        }
    }

    function updateMainButton(mode) {
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

    if (timerMainBtn) timerMainBtn.addEventListener('click', startTimer);
    if (timerResetBtn) timerResetBtn.addEventListener('click', resetTimer);

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
        
        const btn = this.querySelector('button[type="submit"]');
        const input = this.querySelector('input[name="note"]');
        
        if (!input.value.trim()) {
            showToast('Введіть текст нотатки', 'warning');
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        fetch('/api/workout.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Нотатку додано!', 'success');
                addNoteToList(data.note_id, input.value.trim());
                input.value = '';
                updateNoteCount();
            } else {
                showToast(data.error || 'Помилка', 'danger');
            }
        })
        .catch(function() {
            showToast('Помилка з\'єднання', 'danger');
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send"></i>';
        });
    });

    // ===== ФУНКЦИИ ДЛЯ РАБОТЫ С ЗАМЕТКАМИ =====
    function addNoteToList(noteId, noteText) {
        const container = document.getElementById('notesContainer');
        const emptyMsg = container.querySelector('.empty-notes');
        if (emptyMsg) emptyMsg.remove();
        
        const date = new Date();
        const dateStr = date.toLocaleString('uk-UA', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const noteHtml = `
            <div class="note-item mb-2 fade-in" data-note-id="${noteId}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="note-text">${escapeHtml(noteText)}</div>
                        <div class="note-date">
                            <i class="bi bi-clock"></i> ${dateStr}
                        </div>
                    </div>
                    <span class="note-delete" onclick="deleteNote(${noteId})" title="Видалити">
                        <i class="bi bi-x-circle"></i>
                    </span>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', noteHtml);
        updateNoteCount();
    }

    function updateNoteCount() {
        const count = document.querySelectorAll('.note-item').length;
        const badge = document.querySelector('.notes-sidebar .badge');
        if (badge) badge.textContent = count;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Глобальная функция для удаления заметок
    window.deleteNote = function(noteId) {
        if (!confirm('Видалити нотатку?')) return;
        
        const formData = new FormData();
        formData.append('action', 'delete_note');
        formData.append('note_id', noteId);
        
        fetch('/api/workout.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const noteItem = document.querySelector(`.note-item[data-note-id="${noteId}"]`);
                if (noteItem) {
                    noteItem.style.transition = 'all 0.3s ease';
                    noteItem.style.opacity = '0';
                    noteItem.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        noteItem.remove();
                        updateNoteCount();
                        if (document.querySelectorAll('.note-item').length === 0) {
                            const container = document.getElementById('notesContainer');
                            container.innerHTML = `
                                <div class="empty-notes">
                                    <i class="bi bi-journal-text"></i>
                                    <p class="mb-0">Немає нотаток</p>
                                    <small class="text-muted">Додайте першу нотатку нижче</small>
                                </div>
                            `;
                        }
                    }, 300);
                }
                showToast('Нотатку видалено', 'info');
            } else {
                showToast(data.error || 'Помилка', 'danger');
            }
        })
        .catch(function() {
            showToast('Помилка з\'єднання', 'danger');
        });
    };

    // ===== КАЛОРИИ =====
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

    // ===== ИНИЦИАЛИЗАЦИЯ =====
    updateDisplay();
    updateMainButton('start');
    updateCalories();
});

// ===== TOAST (УВЕДОМЛЕНИЯ) =====
function showToast(message, type = 'info') {
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.style.position = 'fixed';
    toastEl.style.top = '20px';
    toastEl.style.right = '20px';
    toastEl.style.zIndex = '9999';
    toastEl.style.borderRadius = '12px';
    toastEl.style.boxShadow = '0 8px 30px rgba(0,0,0,0.2)';
    toastEl.style.minWidth = '250px';
    toastEl.style.maxWidth = '400px';
    
    if (type === 'success') {
        toastEl.style.background = 'linear-gradient(135deg, #00D2A0, #00A87E)';
    } else if (type === 'danger') {
        toastEl.style.background = 'linear-gradient(135deg, #FF6B6B, #EE5A24)';
    } else if (type === 'warning') {
        toastEl.style.background = 'linear-gradient(135deg, #FFB347, #FF8C00)';
    } else {
        toastEl.style.background = 'linear-gradient(135deg, #6C63FF, #5A52D5)';
    }
    
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toastEl);
    
    setTimeout(() => {
        toastEl.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        toastEl.style.opacity = '0';
        toastEl.style.transform = 'translateX(50px)';
        setTimeout(() => toastEl.remove(), 500);
    }, 3000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>