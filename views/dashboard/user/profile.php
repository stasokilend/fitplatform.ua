<?php
require_once 'config/database.php';
require_once 'controllers/ProfileController.php';

$userId = $_SESSION['user_id'];
$profile = getUserProfile($userId);
$message = '';

// Обработка обновления профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $data = [
            'age' => $_POST['age'] ?? $profile['age'],
            'weight' => $_POST['weight'] ?? $profile['weight'],
            'height' => $_POST['height'] ?? $profile['height'],
            'gender' => $_POST['gender'] ?? $profile['gender'],
            'fitness_level' => $_POST['fitness_level'] ?? $profile['fitness_level'],
            'goal_type' => $_POST['goal_type'] ?? $profile['goal_type'],
            'target_weight' => $_POST['target_weight'] ?? $profile['target_weight'],
            'medical_notes' => $_POST['medical_notes'] ?? $profile['medical_notes']
        ];
        
        // Обновляем профиль
        require_once 'controllers/ProfileController.php';
        if (updateProfile($userId, $data)) {
            $message = ['type' => 'success', 'text' => 'Профіль успішно оновлено!'];
            $profile = getUserProfile($userId); // Обновляем данные
        } else {
            $message = ['type' => 'danger', 'text' => 'Помилка оновлення профілю'];
        }
    }
    
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Проверяем текущий пароль
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (password_verify($currentPassword, $user['password_hash'])) {
            if ($newPassword === $confirmPassword && strlen($newPassword) >= 6) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                if ($stmt->execute([$hash, $userId])) {
                    $message = ['type' => 'success', 'text' => 'Пароль успішно змінено!'];
                } else {
                    $message = ['type' => 'danger', 'text' => 'Помилка зміни пароля'];
                }
            } else {
                $message = ['type' => 'danger', 'text' => 'Паролі не співпадають або занадто короткі'];
            }
        } else {
            $message = ['type' => 'danger', 'text' => 'Невірний поточний пароль'];
        }
    }
}

$fitnessLevels = [
    'beginner' => 'Початківець',
    'intermediate' => 'Середній',
    'advanced' => 'Просунутий'
];

$goalTypes = [
    'weight_loss' => 'Зниження ваги',
    'muscle_gain' => 'Набір м\'язової маси',
    'endurance' => 'Витривалість',
    'health' => 'Здоров\'я'
];

$genders = [
    'male' => 'Чоловік',
    'female' => 'Жінка',
    'other' => 'Інше'
];
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-gear text-primary"></i> Налаштування
        </h1>
        <span class="text-muted">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        </span>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $message['type'] === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'; ?> me-2"></i>
            <?php echo $message['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Данные профиля -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-primary"></i> Особисті дані
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ім'я</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Вік</label>
                                <input type="number" name="age" class="form-control" 
                                       value="<?php echo $profile['age'] ?? ''; ?>" 
                                       min="10" max="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Стать</label>
                                <select name="gender" class="form-select" required>
                                    <?php foreach ($genders as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" 
                                            <?php echo ($profile['gender'] ?? '') === $key ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Вага (кг)</label>
                                <input type="number" name="weight" class="form-control" 
                                       value="<?php echo $profile['weight'] ?? ''; ?>" 
                                       step="0.1" min="20" max="300" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Зріст (см)</label>
                                <input type="number" name="height" class="form-control" 
                                       value="<?php echo $profile['height'] ?? ''; ?>" 
                                       min="100" max="250" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Рівень підготовки</label>
                                <select name="fitness_level" class="form-select" required>
                                    <?php foreach ($fitnessLevels as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" 
                                            <?php echo ($profile['fitness_level'] ?? '') === $key ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Основна мета</label>
                                <select name="goal_type" class="form-select" required>
                                    <?php foreach ($goalTypes as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" 
                                            <?php echo ($profile['goal_type'] ?? '') === $key ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Цільова вага (кг)</label>
                                <input type="number" name="target_weight" class="form-control" 
                                       value="<?php echo $profile['target_weight'] ?? ''; ?>" 
                                       step="0.1" min="20" max="300">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Медичні обмеження</label>
                                <textarea name="medical_notes" class="form-control" rows="3" 
                                          placeholder="Наприклад: травма коліна, гіпертонія..."><?php echo $profile['medical_notes'] ?? ''; ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-gradient mt-3">
                            <i class="bi bi-save"></i> Зберегти зміни
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Изменение пароля -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock text-primary"></i> Безпека
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Поточний пароль</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="bi bi-key text-muted"></i>
                                </span>
                                <input type="password" name="current_password" class="form-control border-start-0" 
                                       placeholder="Введіть поточний пароль" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Новий пароль</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="bi bi-key text-muted"></i>
                                </span>
                                <input type="password" name="new_password" class="form-control border-start-0" 
                                       placeholder="Мінімум 6 символів" minlength="6" required>
                            </div>
                            <div class="mt-2">
                                <div class="progress" style="height: 4px;">
                                    <div id="newPasswordStrength" class="progress-bar" style="width: 0%;"></div>
                                </div>
                                <small class="text-muted" id="newStrengthText">Введіть новий пароль</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Підтвердіть пароль</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="bi bi-check-circle text-muted"></i>
                                </span>
                                <input type="password" name="confirm_password" class="form-control border-start-0" 
                                       placeholder="Повторіть пароль" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning btn-gradient w-100">
                            <i class="bi bi-shield-lock"></i> Змінити пароль
                        </button>
                    </form>
                </div>
            </div>

            <!-- Статистика аккаунта -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle text-primary"></i> Інформація про акаунт
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Роль</span>
                        <span class="fw-semibold">
                            <?php echo $profile['role'] === 'admin' ? 'Адміністратор' : ($profile['role'] === 'trainer' ? 'Тренер' : 'Користувач'); ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Дата реєстрації</span>
                        <span class="fw-semibold"><?php echo date('d.m.Y H:i', strtotime($profile['created_at'] ?? 'now')); ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Профіль заповнено</span>
                        <span class="badge bg-<?php echo ($profile['profile_completed'] ?? 0) ? 'success' : 'warning'; ?>">
                            <?php echo ($profile['profile_completed'] ?? 0) ? '✅ Так' : '⏳ Ні'; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Статус</span>
                        <span class="badge bg-<?php echo ($profile['is_active'] ?? 1) ? 'success' : 'danger'; ?>">
                            <?php echo ($profile['is_active'] ?? 1) ? 'Активний' : 'Заблокований'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Индикатор силы нового пароля
    const passwordInput = document.querySelector('input[name="new_password"]');
    const strengthBar = document.getElementById('newPasswordStrength');
    const strengthText = document.getElementById('newStrengthText');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            const width = (strength / 5) * 100;
            strengthBar.style.width = width + '%';
            
            if (password.length === 0) {
                strengthBar.className = 'progress-bar';
                strengthText.textContent = 'Введіть новий пароль';
                return;
            }
            
            let label, className;
            if (strength <= 2) {
                label = 'Слабкий';
                className = 'bg-danger';
            } else if (strength <= 3) {
                label = 'Середній';
                className = 'bg-warning';
            } else if (strength <= 4) {
                label = 'Хороший';
                className = 'bg-info';
            } else {
                label = 'Сильний 🎉';
                className = 'bg-success';
            }
            
            strengthBar.className = 'progress-bar ' + className;
            strengthText.textContent = 'Надійність: ' + label;
            strengthText.className = strength <= 2 ? 'text-danger' : (strength <= 3 ? 'text-warning' : 'text-success');
        });
    }
});
</script>