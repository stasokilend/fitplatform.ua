<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/WorkoutController.php';
require_once __DIR__ . '/../controllers/GamificationController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$workout = new WorkoutController($userId);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- ПОЛУЧЕНИЕ СПИСКА ТРЕНИРОВОК ---
if ($action === 'list') {
    $status = $_GET['status'] ?? null;
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $workouts = $workout->getUserWorkouts($limit, $offset, $status);
    $stats = $workout->getWorkoutStats();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'workouts' => $workouts,
            'stats' => $stats,
            'total' => count($workouts)
        ]
    ]);
    exit;
}

// --- ПОЛУЧЕНИЕ ТРЕНИРОВКИ ---
if ($action === 'get') {
    $workoutId = (int)($_GET['id'] ?? 0);
    if (!$workoutId) {
        echo json_encode(['success' => false, 'error' => 'ID не вказано']);
        exit;
    }
    
    $data = $workout->getWorkout($workoutId);
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Тренування не знайдено']);
        exit;
    }
    
    $data['exercises'] = $workout->getWorkoutExercises($workoutId);
    $data['notes'] = $workout->getNotes($workoutId);
    
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// --- СОЗДАНИЕ ТРЕНИРОВКИ ---
if ($action === 'create') {
    $data = [
        'name' => trim($_POST['name'] ?? 'Моє тренування'),
        'duration' => (int)($_POST['duration'] ?? 30),
        'difficulty' => $_POST['difficulty'] ?? 'intermediate',
        'focus' => $_POST['focus'] ?? 'general'
    ];
    
    if (empty($data['name'])) {
        echo json_encode(['success' => false, 'error' => 'Введіть назву тренування']);
        exit;
    }
    
    $workoutId = $workout->createWorkout($data);
    
    if ($workoutId) {
        // Добавляем упражнения если есть
        $exercises = json_decode($_POST['exercises'] ?? '[]', true);
        foreach ($exercises as $index => $ex) {
            $workout->addExercise($workoutId, $ex['id'], [
                'sets' => $ex['sets'] ?? 3,
                'reps' => $ex['reps'] ?? 10,
                'weight' => $ex['weight'] ?? null,
                'order' => $index
            ]);
        }
        
        echo json_encode(['success' => true, 'workout_id' => $workoutId]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Помилка створення']);
    }
    exit;
}

// --- ПЕРЕКЛЮЧЕНИЕ УПРАЖНЕНИЯ ---
if ($action === 'toggle_exercise') {
    $exerciseId = (int)($_POST['exercise_id'] ?? 0);
    if (!$exerciseId) {
        echo json_encode(['success' => false, 'error' => 'ID не вказано']);
        exit;
    }
    
    $success = $workout->toggleExerciseComplete($exerciseId);
    echo json_encode(['success' => $success]);
    exit;
}

// --- ЗАВЕРШЕНИЕ ТРЕНИРОВКИ (ИСПРАВЛЕНО) ---
if ($action === 'complete') {
    $workoutId = (int)($_POST['workout_id'] ?? 0);
    if (!$workoutId) {
        echo json_encode(['success' => false, 'error' => 'ID не вказано']);
        exit;
    }
    
    // Получаем упражнения тренировки
    $exercises = $workout->getWorkoutExercises($workoutId);
    
    // РАССЧИТЫВАЕМ КАЛОРИИ
    $totalCalories = 0;
    $completedCount = 0;
    $totalExercises = count($exercises);
    
    foreach ($exercises as $exercise) {
        if ($exercise['is_completed']) {
            $completedCount++;
            // Расчет калорий: calories_per_min * duration_min * sets
            $caloriesPerMin = (float)($exercise['calories_per_min'] ?? 0);
            $durationMin = (float)($exercise['duration_min'] ?? 0);
            $sets = (int)($exercise['sets'] ?? 1);
            
            // Если есть длительность упражнения
            if ($durationMin > 0 && $caloriesPerMin > 0) {
                $totalCalories += $caloriesPerMin * $durationMin * $sets;
            } else {
                // Fallback: 5 ккал за упражнение
                $totalCalories += 5;
            }
        }
    }
    
    // Если калории переданы через POST - используем их (приоритет)
    $postedCalories = (int)($_POST['calories'] ?? 0);
    if ($postedCalories > 0) {
        $totalCalories = $postedCalories;
    }
    
    // Округляем
    $totalCalories = (int)round($totalCalories);
    
    // Завершаем тренировку с сохранением калорий
    $success = $workout->completeWorkout($workoutId, $totalCalories);
    
    if ($success) {
        // Обновляем геймификацию
        $gamification = new GamificationController($userId);
        
        // Передаем правильные калории и количество выполненных упражнений
        $gamification->recordWorkoutCompleted(
            $workoutId, 
            $totalCalories, 
            $completedCount, 
            $totalExercises
        );
        
        // ПОЛНАЯ СИНХРОНИЗАЦИЯ ВСЕХ ДОСТИЖЕНИЙ
        $gamification->syncAchievements();
        
        // Создаем уведомление
        require_once __DIR__ . '/../controllers/NotificationController.php';
        $notification = new NotificationController($userId);
        $workoutData = $workout->getWorkout($workoutId);
        $notification->createFromTemplate('workout_completed', [
            'workout_name' => $workoutData['name'] ?? 'Тренування',
            'calories' => $totalCalories
        ]);
        
        echo json_encode([
            'success' => true,
            'calories' => $totalCalories,
            'completed' => $completedCount,
            'total' => $totalExercises
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Помилка завершення тренування']);
    }
    exit;
}

// --- УДАЛЕНИЕ ТРЕНИРОВКИ ---
if ($action === 'delete') {
    $workoutId = (int)($_POST['workout_id'] ?? 0);

    if (!$workoutId) {
        echo json_encode(['success' => false, 'error' => 'ID не вказано']);
        exit;
    }

    $success = $workout->deleteWorkout($workoutId);
    echo json_encode([
        'success' => $success,
        'error' => $success ? null : 'Тренування не знайдено або вже видалено'
    ]);
    exit;
}

// --- ДОБАВЛЕНИЕ ЗАМЕТКИ ---
if ($action === 'add_note') {
    $workoutId = (int)($_POST['workout_id'] ?? 0);
    $note = trim($_POST['note'] ?? '');
    
    if (!$workoutId || !$note) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    $success = $workout->addNote($workoutId, $note);
    echo json_encode(['success' => $success]);
    exit;
}

// --- ПОЛУЧЕНИЕ ШАБЛОНОВ ---
if ($action === 'templates') {
    $templates = $workout->getTemplates();
    echo json_encode(['success' => true, 'data' => $templates]);
    exit;
}

// --- СОЗДАНИЕ ИЗ ШАБЛОНА ---
if ($action === 'create_from_template') {
    $templateId = (int)($_POST['template_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    
    if (!$templateId) {
        echo json_encode(['success' => false, 'error' => 'Шаблон не вказано']);
        exit;
    }
    
    $workoutId = $workout->createFromTemplate($templateId, $name);
    
    if ($workoutId) {
        echo json_encode(['success' => true, 'workout_id' => $workoutId]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Помилка створення з шаблону']);
    }
    exit;
}

// --- СТАТИСТИКА ---
if ($action === 'stats') {
    $stats = $workout->getWorkoutStats();
    echo json_encode(['success' => true, 'data' => $stats]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Невідома дія']);