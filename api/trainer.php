<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/TrainerController.php';
require_once __DIR__ . '/../controllers/ChatController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

if ($_SESSION['user_role'] !== 'trainer' && $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
    exit;
}

$userId = $_SESSION['user_id'];
$trainer = new TrainerController($userId);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- ПЕРЕКЛЮЧЕНИЕ НА ПОЛЬЗОВАТЕЛЯ ---
if ($action === 'switch_to_user') {
    // Проверяем, что пользователь - тренер
    if ($_SESSION['user_role'] !== 'trainer') {
        echo json_encode(['success' => false, 'error' => 'Ви вже є звичайним користувачем']);
        exit;
    }
    
    $result = $trainer->switchToUser();
    
    if ($result['success']) {
        $_SESSION['user_role'] = 'user';
        global $pdo;
        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_name'] = $user['full_name'];
        }
        echo json_encode(['success' => true, 'redirect' => '/dashboard.php?page=settings&tab=role']);
    } else {
        echo json_encode($result);
    }
    exit;
}

// --- ПЕРЕКЛЮЧЕНИЕ НА ТРЕНЕРА ---
if ($action === 'switch_to_trainer') {
    // Проверяем, что пользователь не тренер
    if ($_SESSION['user_role'] === 'trainer') {
        echo json_encode(['success' => false, 'error' => 'Ви вже є тренером']);
        exit;
    }
    
    $result = $trainer->switchToTrainer();
    
    if ($result['success']) {
        $_SESSION['user_role'] = 'trainer';
        global $pdo;
        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_name'] = $user['full_name'];
        }
        echo json_encode(['success' => true, 'redirect' => '/dashboard.php?page=settings&tab=role']);
    } else {
        echo json_encode($result);
    }
    exit;
}

// --- ОСТАЛЬНЫЕ ЭНДПОИНТЫ (требуют роль тренера) ---
if ($_SESSION['user_role'] !== 'trainer' && $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
    exit;
}

// --- ПОЛУЧЕНИЕ СТАТИСТИКИ ---
if ($action === 'stats') {
    $stats = $trainer->getStats();
    echo json_encode(['success' => true, 'data' => $stats]);
    exit;
}

// --- ПОЛУЧЕНИЕ КЛИЕНТОВ ---
if ($action === 'clients') {
    $status = $_GET['status'] ?? 'active';
    $clients = $trainer->getClients($status);
    echo json_encode(['success' => true, 'data' => $clients]);
    exit;
}

// --- ПОЛУЧЕНИЕ КЛИЕНТА ---
if ($action === 'client') {
    $clientId = (int)($_GET['id'] ?? 0);
    if (!$clientId) {
        echo json_encode(['success' => false, 'error' => 'ID клієнта не вказано']);
        exit;
    }
    $client = $trainer->getClient($clientId);
    $progress = $trainer->getClientProgress($clientId);
    echo json_encode([
        'success' => true, 
        'data' => [
            'client' => $client,
            'progress' => $progress
        ]
    ]);
    exit;
}

// --- ПОЛУЧЕНИЕ ПРОГРАММ ---
if ($action === 'programs') {
    $programs = $trainer->getPrograms();
    echo json_encode(['success' => true, 'data' => $programs]);
    exit;
}

// --- СОЗДАНИЕ ПРОГРАММЫ ---
if ($action === 'create_program') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'difficulty' => $_POST['difficulty'] ?? 'beginner',
        'duration_weeks' => (int)($_POST['duration_weeks'] ?? 4),
        'sessions_per_week' => (int)($_POST['sessions_per_week'] ?? 3),
        'is_public' => isset($_POST['is_public']) ? 1 : 0
    ];
    
    if (empty($data['name'])) {
        echo json_encode(['success' => false, 'error' => 'Введіть назву програми']);
        exit;
    }
    
    $programId = $trainer->createProgram($data);
    if ($programId) {
        $exercises = json_decode($_POST['exercises'] ?? '[]', true);
        foreach ($exercises as $ex) {
            $trainer->addExerciseToProgram($programId, $ex['id'], [
                'day' => 1,
                'sets' => 3,
                'reps' => 10,
                'rest_seconds' => 60,
                'order_num' => 0
            ]);
        }
        echo json_encode(['success' => true, 'program_id' => $programId]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Помилка створення програми']);
    }
    exit;
}

// --- ДОБАВЛЕНИЕ УПРАЖНЕНИЯ В ПРОГРАММУ ---
if ($action === 'add_exercise') {
    $programId = (int)($_POST['program_id'] ?? 0);
    $exerciseId = (int)($_POST['exercise_id'] ?? 0);
    $data = [
        'day' => (int)($_POST['day'] ?? 1),
        'sets' => (int)($_POST['sets'] ?? 3),
        'reps' => (int)($_POST['reps'] ?? 10),
        'rest_seconds' => (int)($_POST['rest_seconds'] ?? 60),
        'notes' => $_POST['notes'] ?? null,
        'order_num' => (int)($_POST['order_num'] ?? 0)
    ];
    
    if (!$programId || !$exerciseId) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    $success = $trainer->addExerciseToProgram($programId, $exerciseId, $data);
    echo json_encode(['success' => $success]);
    exit;
}

// --- НАЗНАЧЕНИЕ ПРОГРАММЫ КЛИЕНТУ ---
if ($action === 'assign_program') {
    $clientId = (int)($_POST['client_id'] ?? 0);
    $programId = (int)($_POST['program_id'] ?? 0);
    $startDate = $_POST['start_date'] ?? date('Y-m-d');
    
    if (!$clientId || !$programId) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    $success = $trainer->assignProgramToClient($clientId, $programId, $startDate);
    echo json_encode(['success' => $success]);
    exit;
}

// --- ДОБАВЛЕНИЕ КЛИЕНТА ---
if ($action === 'add_client') {
    $clientId = (int)($_POST['client_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$clientId) {
        echo json_encode(['success' => false, 'error' => 'Виберіть клієнта']);
        exit;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM trainer_clients WHERE trainer_id = ? AND client_id = ?");
    $stmt->execute([$userId, $clientId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Цей клієнт вже доданий']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO trainer_clients (trainer_id, client_id, notes, status, assigned_at)
        VALUES (?, ?, ?, 'active', NOW())
    ");
    $success = $stmt->execute([$userId, $clientId, $notes]);
    
    if ($success) {
        require_once __DIR__ . '/../controllers/ChatController.php';
        $chat = new ChatController($userId);
        $chat->getOrCreateChat($clientId);
    }
    
    echo json_encode(['success' => $success]);
    exit;
}

// --- УДАЛЕНИЕ ПРОГРАММЫ ---
if ($action === 'delete_program') {
    $programId = (int)($_POST['program_id'] ?? 0);
    
    if (!$programId) {
        echo json_encode(['success' => false, 'error' => 'ID програми не вказано']);
        exit;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM trainer_programs WHERE id = ? AND trainer_id = ?");
    $stmt->execute([$programId, $userId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM trainer_programs WHERE id = ? AND trainer_id = ?");
    $success = $stmt->execute([$programId, $userId]);
    
    echo json_encode(['success' => $success]);
    exit;
}

// --- ПОЛУЧЕНИЕ СПИСКА ТРЕНЕРОВ ---
if ($action === 'get_trainers') {
    $trainers = $trainer->getAvailableTrainers();
    echo json_encode(['success' => true, 'data' => $trainers]);
    exit;
}

// --- ПЕРЕДАЧА КЛИЕНТОВ ---
if ($action === 'transfer_clients') {
    $newTrainerId = (int)($_POST['new_trainer_id'] ?? 0);
    
    if (!$newTrainerId) {
        echo json_encode(['success' => false, 'error' => 'Виберіть тренера']);
        exit;
    }
    
    $result = $trainer->transferClients($newTrainerId);
    echo json_encode($result);
    exit;
}

// --- ПОЛУЧЕНИЕ КОЛИЧЕСТВА КЛИЕНТОВ ---
if ($action === 'clients_count') {
    $count = $trainer->getActiveClientsCount();
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

// --- ОБНОВЛЕНИЕ НАСТРОЕК УВЕДОМЛЕНИЙ ---
if ($action === 'update_notifications') {
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
    $workoutReminders = isset($_POST['workout_reminders']) ? 1 : 0;
    $achievementNotifications = isset($_POST['achievement_notifications']) ? 1 : 0;
    $messageNotifications = isset($_POST['message_notifications']) ? 1 : 0;
    $clientActivity = isset($_POST['client_activity']) ? 1 : 0;
    $newClients = isset($_POST['new_clients']) ? 1 : 0;
    
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO trainer_notification_settings 
        (trainer_id, email_notifications, workout_reminders, achievement_notifications, 
         message_notifications, client_activity, new_clients)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            email_notifications = ?,
            workout_reminders = ?,
            achievement_notifications = ?,
            message_notifications = ?,
            client_activity = ?,
            new_clients = ?
    ");
    
    $success = $stmt->execute([
        $userId, $emailNotifications, $workoutReminders, $achievementNotifications,
        $messageNotifications, $clientActivity, $newClients,
        $emailNotifications, $workoutReminders, $achievementNotifications,
        $messageNotifications, $clientActivity, $newClients
    ]);
    
    echo json_encode(['success' => $success]);
    exit;
}

// --- ПОЛУЧЕНИЕ НАСТРОЕК УВЕДОМЛЕНИЙ ---
if ($action === 'get_notifications') {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM trainer_notification_settings WHERE trainer_id = ?
    ");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch();
    
    if (!$settings) {
        $settings = [
            'email_notifications' => 1,
            'workout_reminders' => 1,
            'achievement_notifications' => 1,
            'message_notifications' => 1,
            'client_activity' => 1,
            'new_clients' => 1
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $settings]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Невідома дія']);

// --- ОБНОВЛЕНИЕ ПРОГРАММЫ ---
if ($action === 'update_program') {
    $programId = (int)($_POST['program_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = $_POST['description'] ?? '';
    $difficulty = $_POST['difficulty'] ?? 'intermediate';
    $durationWeeks = (int)($_POST['duration_weeks'] ?? 4);
    $sessionsPerWeek = (int)($_POST['sessions_per_week'] ?? 3);
    $isPublic = (int)($_POST['is_public'] ?? 0);
    $isActive = (int)($_POST['is_active'] ?? 1);
    
    if (!$programId || empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    // Проверяем, что программа принадлежит тренеру
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM trainer_programs WHERE id = ? AND trainer_id = ?");
    $stmt->execute([$programId, $userId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Доступ заборонено']);
        exit;
    }
    
    // Обновляем программу
    $stmt = $pdo->prepare("
        UPDATE trainer_programs 
        SET name = ?, description = ?, difficulty = ?, 
            duration_weeks = ?, sessions_per_week = ?, 
            is_public = ?, is_active = ?
        WHERE id = ?
    ");
    $success = $stmt->execute([
        $name, $description, $difficulty,
        $durationWeeks, $sessionsPerWeek,
        $isPublic, $isActive,
        $programId
    ]);
    
    if (!$success) {
        echo json_encode(['success' => false, 'error' => 'Помилка оновлення програми']);
        exit;
    }
    
    // Удаляем старые упражнения
    $stmt = $pdo->prepare("DELETE FROM program_exercises WHERE program_id = ?");
    $stmt->execute([$programId]);
    
    // Добавляем новые упражнения
    $exercises = json_decode($_POST['exercises'] ?? '[]', true);
    foreach ($exercises as $ex) {
        $stmt = $pdo->prepare("
            INSERT INTO program_exercises (program_id, exercise_id, day, sets, reps, rest_seconds, notes, order_num)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $programId,
            $ex['exercise_id'],
            $ex['day'] ?? 1,
            $ex['sets'] ?? 3,
            $ex['reps'] ?? 10,
            $ex['rest_seconds'] ?? 60,
            $ex['notes'] ?? null,
            $ex['order_num'] ?? 0
        ]);
    }
    
    echo json_encode(['success' => true]);
    exit;

    // --- ЗАПРОС ПРОГРАММЫ ---
if ($action === 'request_program') {
    $programId = (int)($_POST['program_id'] ?? 0);
    
    if (!$programId) {
        echo json_encode(['success' => false, 'error' => 'ID програми не вказано']);
        exit;
    }
    
    // Проверяем, что программа существует и публична
    global $pdo;
    $stmt = $pdo->prepare("SELECT trainer_id, name FROM trainer_programs WHERE id = ? AND is_public = 1 AND is_active = 1");
    $stmt->execute([$programId]);
    $program = $stmt->fetch();
    
    if (!$program) {
        echo json_encode(['success' => false, 'error' => 'Програма не знайдена']);
        exit;
    }
    
    // Проверяем, не назначена ли уже
    $stmt = $pdo->prepare("SELECT id FROM client_programs WHERE client_id = ? AND program_id = ? AND status = 'active'");
    $stmt->execute([$userId, $programId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Ця програма вже призначена вам']);
        exit;
    }
    
    // Проверяем, есть ли уже запрос
    $stmt = $pdo->prepare("SELECT id FROM client_programs WHERE client_id = ? AND program_id = ? AND status = 'pending'");
    $stmt->execute([$userId, $programId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Запит на цю програму вже надіслано']);
        exit;
    }
    
    // Создаем запрос
    $stmt = $pdo->prepare("
        INSERT INTO client_programs (client_id, program_id, trainer_id, status)
        VALUES (?, ?, ?, 'pending')
    ");
    $success = $stmt->execute([$userId, $programId, $program['trainer_id']]);
    
    if ($success) {
        // Создаем уведомление для тренера
        require_once __DIR__ . '/../controllers/NotificationController.php';
        $notification = new NotificationController($program['trainer_id']);
        $notification->createFromTemplate('new_program_request', [
            'client_name' => $_SESSION['user_name'] ?? 'Користувач',
            'program_name' => $program['name']
        ]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Помилка створення запиту']);
    }
    exit;
}

// --- ПРИНЯТИЕ/ОТКЛОНЕНИЕ ЗАПРОСА ПРОГРАММЫ (для тренера) ---
if ($action === 'handle_program_request') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $status = $_POST['status'] ?? ''; // 'approved' или 'rejected'
    
    if (!$requestId || !in_array($status, ['approved', 'rejected'])) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    global $pdo;
    
    // Проверяем, что запрос принадлежит тренеру
    $stmt = $pdo->prepare("
        SELECT cp.*, u.full_name as client_name, tp.name as program_name
        FROM client_programs cp
        JOIN users u ON cp.client_id = u.id
        JOIN trainer_programs tp ON cp.program_id = tp.id
        WHERE cp.id = ? AND cp.trainer_id = ? AND cp.status = 'pending'
    ");
    $stmt->execute([$requestId, $userId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Запит не знайдено']);
        exit;
    }
    
    if ($status === 'approved') {
        $stmt = $this->pdo->prepare("
            UPDATE client_programs 
            SET status = 'active', start_date = CURDATE()
            WHERE id = ?
        ");
        $success = $stmt->execute([$requestId]);
        
        if ($success) {
            // Уведомление пользователю
            require_once __DIR__ . '/../controllers/NotificationController.php';
            $notification = new NotificationController($request['client_id']);
            $notification->createFromTemplate('program_approved', [
                'program_name' => $request['program_name'],
                'trainer_name' => $_SESSION['user_name']
            ]);
        }
    } else {
        $stmt = $this->pdo->prepare("
            UPDATE client_programs 
            SET status = 'cancelled'
            WHERE id = ?
        ");
        $success = $stmt->execute([$requestId]);
    }
    
    echo json_encode(['success' => $success]);
    exit;
}
}
?>