<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../controllers/TrainerController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необхідно авторизуватися']);
    exit;
}

$userId = $_SESSION['user_id'];
$trainer = new TrainerController($userId);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

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

// --- ОТПРАВКА СООБЩЕНИЯ ---
if ($action === 'send_message') {
    $clientId = (int)($_POST['client_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    if (!$clientId || !$message) {
        echo json_encode(['success' => false, 'error' => 'Недостатньо даних']);
        exit;
    }
    
    $success = $trainer->sendMessage($clientId, $message);
    echo json_encode(['success' => $success]);
    exit;
}

// --- ПОЛУЧЕНИЕ СООБЩЕНИЙ ---
if ($action === 'messages') {
    $clientId = (int)($_GET['client_id'] ?? 0);
    if (!$clientId) {
        echo json_encode(['success' => false, 'error' => 'ID клієнта не вказано']);
        exit;
    }
    
    $messages = $trainer->getMessages($clientId);
    $trainer->markMessagesAsRead($clientId);
    echo json_encode(['success' => true, 'data' => $messages]);
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
        // Добавляем упражнения если есть
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
    
    // Проверяем, не добавлен ли уже
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

// --- ПЕРЕКЛЮЧЕНИЕ НА ПОЛЬЗОВАТЕЛЯ ---
if ($action === 'switch_to_user') {
    // Проверяем, что пользователь - тренер
    if ($_SESSION['user_role'] !== 'trainer') {
        echo json_encode(['success' => false, 'error' => 'Ви вже є звичайним користувачем']);
        exit;
    }
    
    $result = $trainer->switchToUser();
    
    // Если успешно, обновляем сессию и возвращаем успех
    if ($result['success']) {
        $_SESSION['user_role'] = 'user';
        // Обновляем имя пользователя в сессии
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
    
    // Если успешно, обновляем сессию
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

// --- ОБНОВЛЕНИЕ НАСТРОЕК УВЕДОМЛЕНИЙ ТРЕНЕРА ---
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
?>