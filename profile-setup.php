<?php
require_once 'includes/session.php';
require_once 'includes/Csrf.php';
requireLogin();

require_once 'config/database.php';

// Получаем текущие данные профиля
$stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT full_name, email, role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Недійсний CSRF-токен. Оновіть сторінку і спробуйте ще раз.';
        header('Location: ' . url('/profile-setup.php'));
        exit;
    }
    require_once 'controllers/ProfileController.php';
    require_once 'controllers/TrainerController.php';
    
    $data = [
        'age' => !empty($_POST['age']) ? (int)$_POST['age'] : null,
        'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null,
        'height' => !empty($_POST['height']) ? (int)$_POST['height'] : null,
        'gender' => $_POST['gender'] ?? null,
        'fitness_level' => $_POST['fitness_level'] ?? 'beginner',
        'goal_type' => $_POST['goal_type'] ?? 'health',
        'target_weight' => !empty($_POST['target_weight']) ? (float)$_POST['target_weight'] : null,
        'medical_notes' => $_POST['medical_notes'] ?? null
    ];
    
    $roleType = $_POST['role_type'] ?? 'user';
    
    if (updateProfile($_SESSION['user_id'], $data)) {
        // Обновляем роль
        if ($roleType === 'trainer') {
            $trainer = new TrainerController($_SESSION['user_id']);
            $result = $trainer->switchToTrainer();
            if ($result['success']) {
                $_SESSION['user_role'] = 'trainer';
            }
        } else {
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $currentRole = $stmt->fetch()['role'] ?? 'user';
            
            if ($currentRole === 'trainer') {
                $trainer = new TrainerController($_SESSION['user_id']);
                $trainer->switchToUser();
                $_SESSION['user_role'] = 'user';
            } else {
                $_SESSION['user_role'] = 'user';
            }
        }
        
        $_SESSION['success'] = 'Профіль успішно заповнено! Ласкаво просимо до FitPlatform! 🎉';
        header('Location: /dashboard.php');
        exit;
    } else {
        $_SESSION['error'] = 'Помилка збереження профілю. Спробуйте ще раз.';
    }
}

$pageTitle = 'Заповнення профілю';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitPlatform - Заповнення профілю</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="<?= asset('/assets/css/style.css'); ?>" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Inter', sans-serif;
        }
        .setup-container {
            max-width: 820px;
            width: 100%;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            padding: 40px;
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .setup-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .setup-header .logo {
            display: inline-block;
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #6C63FF 0%, #FF6584 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        .setup-header h3 {
            font-weight: 700;
            color: #1A1A2E;
            margin-bottom: 4px;
        }
        .setup-header p {
            color: #6C757D;
            font-size: 0.95rem;
        }
        .setup-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 40px;
            padding: 0 20px;
        }
        .setup-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 40px;
            right: 40px;
            height: 3px;
            background: #e9ecef;
            z-index: 0;
        }
        .setup-steps .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            cursor: pointer;
            flex: 1;
        }
        .setup-steps .step .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6C757D;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 3px solid transparent;
        }
        .setup-steps .step.active .step-number {
            background: #6C63FF;
            color: #fff;
            border-color: #6C63FF;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        }
        .setup-steps .step.completed .step-number {
            background: #00D2A0;
            color: #fff;
            border-color: #00D2A0;
        }
        .setup-steps .step .step-label {
            margin-top: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6C757D;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .setup-steps .step.active .step-label {
            color: #6C63FF;
        }
        .setup-steps .step.completed .step-label {
            color: #00D2A0;
        }
        .form-section {
            display: none;
            animation: fadeIn 0.4s ease-out;
        }
        .form-section.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .form-section .section-title {
            font-weight: 700;
            font-size: 1.2rem;
            color: #1A1A2E;
            margin-bottom: 4px;
        }
        .form-section .section-subtitle {
            color: #6C757D;
            font-size: 0.9rem;
            margin-bottom: 24px;
        }
        .form-control, .form-select {
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid #E8ECF1;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6C63FF;
            box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.12);
        }
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1A1A2E;
            margin-bottom: 6px;
        }
        .input-group-text {
            background: transparent;
            border: 2px solid #E8ECF1;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #6C757D;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        .goal-card, .role-card {
            padding: 16px 20px;
            border: 2px solid #E8ECF1;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
            text-align: center;
            height: 100%;
        }
        .goal-card:hover, .role-card:hover {
            border-color: #6C63FF;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.08);
        }
        .goal-card.selected, .role-card.selected {
            border-color: #6C63FF;
            background: rgba(108, 99, 255, 0.05);
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.12);
            position: relative;
        }
        .goal-card.selected::after, .role-card.selected::after {
            content: '✓';
            position: absolute;
            top: 8px;
            right: 12px;
            font-size: 1rem;
            color: #6C63FF;
            font-weight: 700;
        }
        .goal-card .goal-icon, .role-card .role-icon {
            font-size: 2rem;
            display: block;
            margin-bottom: 8px;
        }
        .goal-card .goal-name, .role-card .role-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1A1A2E;
        }
        .goal-card .goal-desc, .role-card .role-desc {
            font-size: 0.75rem;
            color: #6C757D;
            margin-top: 2px;
        }
        .role-card .role-desc ul {
            text-align: left;
            padding-left: 0;
            margin-bottom: 0;
        }
        .role-card .role-desc li {
            font-size: 0.8rem;
            padding: 2px 0;
        }
        .role-card .role-desc li i {
            margin-right: 6px;
        }
        .btn-primary-gradient {
            background: linear-gradient(135deg, #6C63FF 0%, #5A52D5 100%);
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 99, 255, 0.3);
            color: #fff;
        }
        .btn-outline-secondary-custom {
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            border: 2px solid #E8ECF1;
            background: #fff;
            color: #6C757D;
            transition: all 0.3s ease;
        }
        .btn-outline-secondary-custom:hover {
            border-color: #6C63FF;
            color: #6C63FF;
            background: rgba(108, 99, 255, 0.04);
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.15) !important;
        }
        @media (max-width: 576px) {
            .setup-container { padding: 24px 16px; }
            .setup-steps { padding: 0 10px; gap: 4px; }
            .setup-steps .step .step-number { width: 32px; height: 32px; font-size: 0.75rem; }
            .setup-steps .step .step-label { font-size: 0.6rem; }
            .goal-card, .role-card { padding: 12px 16px; }
            .goal-card .goal-icon, .role-card .role-icon { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="setup-container">
    <div class="setup-header">
        <div class="logo"><i class="bi bi-heart-pulse-fill"></i> FitPlatform</div>
        <h3>Ласкаво просимо, <?php echo htmlspecialchars($user['full_name'] ?? 'Користувач'); ?>! 👋</h3>
        <p>Давайте налаштуємо ваш профіль для персоналізованих тренувань</p>
    </div>

    <div class="setup-steps" id="steps">
        <div class="step active" data-step="1">
            <div class="step-number">1</div>
            <span class="step-label">Особисті дані</span>
        </div>
        <div class="step" data-step="2">
            <div class="step-number">2</div>
            <span class="step-label">Цілі</span>
        </div>
        <div class="step" data-step="2_5">
            <div class="step-number">3</div>
            <span class="step-label">Роль</span>
        </div>
        <div class="step" data-step="3">
            <div class="step-number">4</div>
            <span class="step-label">Здоров'я</span>
        </div>
        <div class="step" data-step="4">
            <div class="step-number">5</div>
            <span class="step-label">Готово!</span>
        </div>
    </div>

    <form method="POST" id="profileForm" novalidate>
            <?= csrfField(); ?>
        <!-- Step 1: Personal Data -->
        <div class="form-section active" data-section="1">
            <div class="section-title"><i class="bi bi-person text-primary"></i> Особисті дані</div>
            <div class="section-subtitle">Розкажіть трохи про себе</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Повне ім'я</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" disabled>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Вік</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                        <input type="number" name="age" class="form-control" value="<?php echo $profile['age'] ?? ''; ?>" min="10" max="100" placeholder="Років" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Вага (кг)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-weight-scale"></i></span>
                        <input type="number" name="weight" class="form-control" value="<?php echo $profile['weight'] ?? ''; ?>" step="0.1" min="20" max="300" placeholder="кг" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Зріст (см)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                        <input type="number" name="height" class="form-control" value="<?php echo $profile['height'] ?? ''; ?>" min="100" max="250" placeholder="см" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Стать</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Оберіть...</option>
                        <option value="male" <?php echo ($profile['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Чоловік</option>
                        <option value="female" <?php echo ($profile['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Жінка</option>
                        <option value="other" <?php echo ($profile['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Інше</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Рівень підготовки</label>
                    <select name="fitness_level" class="form-select" required>
                        <option value="beginner" <?php echo ($profile['fitness_level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Початківець</option>
                        <option value="intermediate" <?php echo ($profile['fitness_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Середній</option>
                        <option value="advanced" <?php echo ($profile['fitness_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Просунутий</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-primary-gradient" onclick="nextStep()">Далі <i class="bi bi-arrow-right ms-2"></i></button>
            </div>
        </div>

        <!-- Step 2: Goals -->
        <div class="form-section" data-section="2">
            <div class="section-title"><i class="bi bi-bullseye text-primary"></i> Ваші цілі</div>
            <div class="section-subtitle">Оберіть основну мету тренувань</div>
            <!-- Вместо goal cards, добавляем слайдеры -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            <i class="bi bi-fire text-danger"></i> Зниження ваги
        </label>
        <input type="range" class="form-range goal-slider" id="goal_weight_loss" 
               min="0" max="100" value="0" step="5">
        <div class="d-flex justify-content-between small text-muted">
            <span>Не важливо</span>
            <span id="goal_weight_loss_label">0%</span>
            <span>Дуже важливо</span>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            <i class="bi bi-dumbbell text-primary"></i> Набір м'язової маси
        </label>
        <input type="range" class="form-range goal-slider" id="goal_muscle_gain" 
               min="0" max="100" value="0" step="5">
        <div class="d-flex justify-content-between small text-muted">
            <span>Не важливо</span>
            <span id="goal_muscle_gain_label">0%</span>
            <span>Дуже важливо</span>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            <i class="bi bi-arrow-repeat text-success"></i> Витривалість
        </label>
        <input type="range" class="form-range goal-slider" id="goal_endurance" 
               min="0" max="100" value="0" step="5">
        <div class="d-flex justify-content-between small text-muted">
            <span>Не важливо</span>
            <span id="goal_endurance_label">0%</span>
            <span>Дуже важливо</span>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            <i class="bi bi-heart-pulse text-info"></i> Загальне здоров'я
        </label>
        <input type="range" class="form-range goal-slider" id="goal_health" 
               min="0" max="100" value="0" step="5">
        <div class="d-flex justify-content-between small text-muted">
            <span>Не важливо</span>
            <span id="goal_health_label">0%</span>
            <span>Дуже важливо</span>
        </div>
    </div>
</div>
<input type="hidden" name="goal_weights" id="goalWeightsInput" value='{"weight_loss":0,"muscle_gain":0,"endurance":0,"health":0}'>

        <!-- Step 2.5: Role Selection -->
        <div class="form-section" data-section="2_5">
            <div class="section-title"><i class="bi bi-person-badge text-primary"></i> Оберіть роль</div>
            <div class="section-subtitle">Як ви плануєте використовувати платформу?</div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="role-card <?php echo ($user['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>" data-role="user">
                        <div class="role-icon">💪</div>
                        <div class="role-name">Користувач</div>
                        <div class="role-desc">
                            <ul class="list-unstyled small text-muted">
                                <li><i class="bi bi-check-circle text-success"></i> Персоналізовані тренування</li>
                                <li><i class="bi bi-check-circle text-success"></i> Відстеження прогресу</li>
                                <li><i class="bi bi-check-circle text-success"></i> Досягнення та гейміфікація</li>
                                <li><i class="bi bi-check-circle text-success"></i> Моніторинг здоров'я</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="role-card <?php echo ($user['role'] ?? '') === 'trainer' ? 'selected' : ''; ?>" data-role="trainer">
                        <div class="role-icon">🏋️</div>
                        <div class="role-name">Тренер</div>
                        <div class="role-desc">
                            <ul class="list-unstyled small text-muted">
                                <li><i class="bi bi-check-circle text-success"></i> Керування клієнтами</li>
                                <li><i class="bi bi-check-circle text-success"></i> Створення програм</li>
                                <li><i class="bi bi-check-circle text-success"></i> Відстеження прогресу клієнтів</li>
                                <li><i class="bi bi-check-circle text-success"></i> Чат з клієнтами</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="role_type" id="roleType" value="<?php echo $user['role'] ?? 'user'; ?>">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Зверніть увагу:</strong> Ви зможете змінити роль у будь-який момент у налаштуваннях аккаунта.
            </div>
            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary-custom" onclick="prevStep()"><i class="bi bi-arrow-left me-2"></i> Назад</button>
                <button type="button" class="btn btn-primary-gradient" onclick="nextStep()">Далі <i class="bi bi-arrow-right ms-2"></i></button>
            </div>
        </div>

        <!-- Step 3: Health -->
        <div class="form-section" data-section="3">
            <div class="section-title"><i class="bi bi-heart-pulse text-primary"></i> Стан здоров'я</div>
            <div class="section-subtitle">Важливо для безпечних тренувань</div>
            <div class="mb-3">
                <label class="form-label">Медичні обмеження</label>
                <textarea name="medical_notes" class="form-control" rows="4" placeholder="Наприклад: травма коліна, гіпертонія, проблеми зі спиною..."><?php echo $profile['medical_notes'] ?? ''; ?></textarea>
                <small class="text-muted">Ця інформація допоможе уникнути травм під час тренувань</small>
            </div>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Конфіденційність:</strong> Ваші медичні дані захищені та доступні тільки вам та вашому тренеру (якщо він призначений).
            </div>
            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary-custom" onclick="prevStep()"><i class="bi bi-arrow-left me-2"></i> Назад</button>
                <button type="button" class="btn btn-primary-gradient" onclick="nextStep()">Далі <i class="bi bi-arrow-right ms-2"></i></button>
            </div>
        </div>

        <!-- Step 4: Complete -->
        <div class="form-section" data-section="4">
            <div class="text-center py-4">
                <div class="display-1 text-success mb-3"><i class="bi bi-check-circle-fill"></i></div>
                <h3 class="fw-bold">Все готово! 🎉</h3>
                <p class="text-muted mb-4">Ви успішно налаштували свій профіль. Тепер ви можете почати тренуватися!</p>
                <div class="row g-3 justify-content-center mb-4">
                    <div class="col-auto"><div class="bg-light p-3 rounded-3"><div class="small text-muted">Вік</div><div class="fw-bold" id="summaryAge">-</div></div></div>
                    <div class="col-auto"><div class="bg-light p-3 rounded-3"><div class="small text-muted">Вага</div><div class="fw-bold" id="summaryWeight">-</div></div></div>
                    <div class="col-auto"><div class="bg-light p-3 rounded-3"><div class="small text-muted">Зріст</div><div class="fw-bold" id="summaryHeight">-</div></div></div>
                    <div class="col-auto"><div class="bg-light p-3 rounded-3"><div class="small text-muted">Мета</div><div class="fw-bold" id="summaryGoal">-</div></div></div>
                    <div class="col-auto"><div class="bg-light p-3 rounded-3"><div class="small text-muted">Роль</div><div class="fw-bold" id="summaryRole">-</div></div></div>
                </div>
                <button type="submit" class="btn btn-primary-gradient btn-lg px-5"><i class="bi bi-rocket-fill me-2"></i> Розпочати тренування!</button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = '1';
    const stepMap = { '1': '2', '2': '2_5', '2_5': '3', '3': '4', '4': '4' };
    const prevStepMap = { '1': '1', '2': '1', '2_5': '2', '3': '2_5', '4': '3' };
    const stepIndexMap = { '1': 0, '2': 1, '2_5': 2, '3': 3, '4': 4 };

    // Goal cards
    const goalCards = document.querySelectorAll('.goal-card');
    const goalInput = document.getElementById('goalType');
    goalCards.forEach(card => {
        card.addEventListener('click', function() {
            goalCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            goalInput.value = this.dataset.goal;
        });
    });

    // Role cards
    const roleCards = document.querySelectorAll('.role-card');
    const roleInput = document.getElementById('roleType');
    roleCards.forEach(card => {
        card.addEventListener('click', function() {
            roleCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            roleInput.value = this.dataset.role;
        });
    });

    window.nextStep = function() {
        if (validateStep(currentStep)) {
            const next = stepMap[currentStep];
            if (next && next !== currentStep) {
                currentStep = next;
                updateSteps();
            }
        }
    };

    window.prevStep = function() {
        const prev = prevStepMap[currentStep];
        if (prev && prev !== currentStep) {
            currentStep = prev;
            updateSteps();
        }
    };

    function updateSteps() {
        document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
        const activeSection = document.querySelector(`.form-section[data-section="${currentStep}"]`);
        if (activeSection) activeSection.classList.add('active');

        const currentIndex = stepIndexMap[currentStep] || 0;
        document.querySelectorAll('.step').forEach((el, index) => {
            el.classList.remove('active', 'completed');
            if (el.dataset.step == currentStep) {
                el.classList.add('active');
            } else if (index < currentIndex) {
                el.classList.add('completed');
            }
        });

        if (currentStep === '4') updateSummary();
    }

    function validateStep(step) {
        if (step === '2_5' || step === '4') return true;
        const section = document.querySelector(`.form-section[data-section="${step}"]`);
        if (!section) return true;
        const inputs = section.querySelectorAll('input[required], select[required]');
        let valid = true;
        inputs.forEach(input => {
            if (!input.value || input.value === '') {
                input.classList.add('is-invalid');
                valid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        if (!valid) {
            const firstInvalid = section.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            showToast('Будь ласка, заповніть всі обов\'язкові поля', 'warning');
        }
        return valid;
    }

    function updateSummary() {
        const age = document.querySelector('input[name="age"]')?.value || '-';
        const weight = document.querySelector('input[name="weight"]')?.value || '-';
        const height = document.querySelector('input[name="height"]')?.value || '-';
        const goalMap = { 'weight_loss': 'Зниження ваги', 'muscle_gain': 'Набір маси', 'endurance': 'Витривалість', 'health': 'Здоров\'я' };
        const goal = goalMap[document.getElementById('goalType').value] || '-';
        const roleMap = { 'user': '💪 Користувач', 'trainer': '🏋️ Тренер' };
        const role = roleMap[document.getElementById('roleType').value] || '💪 Користувач';
        document.getElementById('summaryAge').textContent = age + ' років';
        document.getElementById('summaryWeight').textContent = weight + ' кг';
        document.getElementById('summaryHeight').textContent = height + ' см';
        document.getElementById('summaryGoal').textContent = goal;
        document.getElementById('summaryRole').textContent = role;
    }

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateStep(currentStep)) this.submit();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            if (currentStep === '4') document.getElementById('profileForm').submit();
            else nextStep();
        }
    });

    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', function() { this.classList.remove('is-invalid'); });
        el.addEventListener('change', function() { this.classList.remove('is-invalid'); });
    });

    setTimeout(() => validateStep('1'), 500);
});

function showToast(message, type = 'info') {
    const existing = document.querySelector('.custom-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = `custom-toast position-fixed top-0 start-50 translate-middle-x mt-3 p-3 rounded-3 shadow-lg`;
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.style.maxWidth = '90%';
    toast.style.background = type === 'warning' ? '#fff3cd' : '#cce5ff';
    toast.style.border = type === 'warning' ? '2px solid #ffc107' : '2px solid #0d6efd';
    toast.style.color = type === 'warning' ? '#856404' : '#004085';
    toast.style.animation = 'fadeInUp 0.3s ease-out';
    toast.innerHTML = `<div class="d-flex align-items-center"><i class="bi bi-${type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i><span>${message}</span><button type="button" class="btn-close ms-3" onclick="this.parentElement.parentElement.remove()"></button></div>`;
    document.body.appendChild(toast);
    setTimeout(() => { if (toast.parentElement) { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); } }, 4000);
}
// Обновление слайдеров
document.querySelectorAll('.goal-slider').forEach(function(slider) {
    slider.addEventListener('input', function() {
        const label = document.getElementById(this.id + '_label');
        if (label) label.textContent = this.value + '%';
        updateGoalWeights();
    });
});

function updateGoalWeights() {
    const weights = {
        weight_loss: parseInt(document.getElementById('goal_weight_loss').value) / 100,
        muscle_gain: parseInt(document.getElementById('goal_muscle_gain').value) / 100,
        endurance: parseInt(document.getElementById('goal_endurance').value) / 100,
        health: parseInt(document.getElementById('goal_health').value) / 100
    };
    document.getElementById('goalWeightsInput').value = JSON.stringify(weights);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>