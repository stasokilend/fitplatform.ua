<?php
require_once 'includes/session.php';
requireLogin();

// Проверяем, что пользователь залогинен
if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

require_once 'config/database.php';
require_once 'controllers/ProfileController.php';

$userId = $_SESSION['user_id'];
$error = null;
$success = null;

// Получаем текущие данные профиля
$profile = getUserProfile($userId);

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'age' => (int)$_POST['age'],
        'weight' => (float)$_POST['weight'],
        'height' => (int)$_POST['height'],
        'gender' => $_POST['gender'] ?? '',
        'fitness_level' => $_POST['fitness_level'] ?? 'beginner',
        'goal_type' => $_POST['goal_type'] ?? 'health',
        'target_weight' => !empty($_POST['target_weight']) ? (float)$_POST['target_weight'] : null,
        'medical_notes' => trim($_POST['medical_notes'] ?? '')
    ];
    
    // Валидация
    if (empty($data['age']) || $data['age'] < 10 || $data['age'] > 100) {
        $error = 'Введіть коректний вік (10-100 років)';
    } elseif (empty($data['weight']) || $data['weight'] < 20 || $data['weight'] > 300) {
        $error = 'Введіть коректну вагу (20-300 кг)';
    } elseif (empty($data['height']) || $data['height'] < 100 || $data['height'] > 250) {
        $error = 'Введіть коректний зріст (100-250 см)';
    } elseif (empty($data['gender'])) {
        $error = 'Оберіть стать';
    } else {
        if (updateProfile($userId, $data)) {
            $success = 'Профіль успішно заповнено!';
            
            // Обновляем данные профиля
            $profile = getUserProfile($userId);
            
            // Перенаправление в кабинет
            header('Location: /dashboard.php');
            exit;
        } else {
            $error = 'Помилка збереження профілю';
        }
    }
}

$pageTitle = 'Заповнення профілю';
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-person-gear"></i> Заповніть профіль
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <p class="text-muted text-center mb-4">
                        Ці дані допоможуть нам створити персоналізовану програму тренувань
                    </p>
                    
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Вік</label>
                                <input type="number" name="age" class="form-control" 
                                       value="<?php echo htmlspecialchars($profile['age'] ?? ''); ?>" 
                                       min="10" max="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Вага (кг)</label>
                                <input type="number" name="weight" class="form-control" 
                                       value="<?php echo htmlspecialchars($profile['weight'] ?? ''); ?>" 
                                       step="0.1" min="20" max="300" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Зріст (см)</label>
                                <input type="number" name="height" class="form-control" 
                                       value="<?php echo htmlspecialchars($profile['height'] ?? ''); ?>" 
                                       min="100" max="250" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Стать</label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Виберіть...</option>
                                    <option value="male" <?php echo ($profile['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Чоловік</option>
                                    <option value="female" <?php echo ($profile['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Жінка</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Рівень підготовки</label>
                                <select name="fitness_level" class="form-select" required>
                                    <option value="">Виберіть...</option>
                                    <option value="beginner" <?php echo ($profile['fitness_level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Початківець</option>
                                    <option value="intermediate" <?php echo ($profile['fitness_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Середній</option>
                                    <option value="advanced" <?php echo ($profile['fitness_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Просунутий</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Основна мета</label>
                                <select name="goal_type" class="form-select" required>
                                    <option value="">Виберіть...</option>
                                    <option value="weight_loss" <?php echo ($profile['goal_type'] ?? '') === 'weight_loss' ? 'selected' : ''; ?>>Зниження ваги</option>
                                    <option value="muscle_gain" <?php echo ($profile['goal_type'] ?? '') === 'muscle_gain' ? 'selected' : ''; ?>>Набір м'язової маси</option>
                                    <option value="endurance" <?php echo ($profile['goal_type'] ?? '') === 'endurance' ? 'selected' : ''; ?>>Витривалість</option>
                                    <option value="health" <?php echo ($profile['goal_type'] ?? '') === 'health' ? 'selected' : ''; ?>>Здоров'я</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Цільова вага (кг)</label>
                                <input type="number" name="target_weight" class="form-control" 
                                       value="<?php echo htmlspecialchars($profile['target_weight'] ?? ''); ?>" 
                                       step="0.1" min="20" max="300">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Медичні обмеження (при наявності)</label>
                                <textarea name="medical_notes" class="form-control" rows="3" 
                                          placeholder="Наприклад: травма коліна, гіпертонія..."><?php echo htmlspecialchars($profile['medical_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mt-4">
                            <i class="bi bi-check-circle"></i> Зберегти та продовжити
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once 'views/layout.php';
?>