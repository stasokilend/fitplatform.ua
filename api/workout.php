<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/RateLimiter.php';
require_once __DIR__ . '/../controllers/WorkoutController.php';
require_once __DIR__ . '/../controllers/GamificationController.php';

header('Content-Type: application/json');

$rateKey = 'workout-api:' . ($_SESSION['user_id'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'guest'));
if (!RateLimiter::hit($rateKey, 120, 60)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Забагато запитів. Спробуйте пізніше.']);
    exit;
}

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

// --- ЗАВЕРШЕНИЕ ТРЕНИРОВКИ ---
if ($action === 'complete') {
    $workoutId = (int)($_POST['workout_id'] ?? 0);
    if (!$workoutId) {
        echo json_encode(['success' => false, 'error' => 'ID не вказано']);
        exit;
    }
    
    $success = $workout->completeWorkout($workoutId);
    
    if ($success) {
        // Обновляем геймификацию
        $gamification = new GamificationController($userId);
        $exercises = $workout->getWorkoutExercises($workoutId);
        $calories = (int)($_POST['calories'] ?? 0);
        $completed = count(array_filter($exercises, function($ex) {
            return $ex['is_completed'];
        }));
        
        $gamification->updateStats($calories, $completed, true);
        
        // Создаем уведомление
        require_once __DIR__ . '/../controllers/NotificationController.php';
        $notification = new NotificationController($userId);
        $workoutData = $workout->getWorkout($workoutId);
        $notification->createFromTemplate('workout_completed', [
            'workout_name' => $workoutData['name'] ?? 'Тренування',
            'calories' => $calories
        ]);
    }
    
    echo json_encode(['success' => $success]);
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
?>