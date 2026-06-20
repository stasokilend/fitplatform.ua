<?php
require_once 'config/database.php';
require_once 'controllers/ProfileController.php';
require_once 'controllers/GoogleFitController.php';

$userId = $_SESSION['user_id'];
$profile = getUserProfile($userId);
$googleFit = new GoogleFitController($userId);
$isGoogleFitConnected = $googleFit->isConnected();

$activeTab = $_GET['tab'] ?? 'profile';
$message = '';

// Обработка обновления профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
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
        
        require_once 'controllers/ProfileController.php';
        $result = updateProfile($userId, $data);
        
        if ($result) {
            $message = ['type' => 'success', 'text' => 'Профіль успішно оновлено!'];
            $profile = getUserProfile($userId);
        } else {
            $message = ['type' => 'danger', 'text' => 'Помилка оновлення профілю. Спробуйте ще раз.'];
        }
    }
    
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
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
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $message['type'] === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'; ?> me-2"></i>
            <?php echo $message['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Вкладки -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $activeTab === 'profile' ? 'active' : ''; ?>" 
               href="?page=settings&tab=profile" role="tab">
                <i class="bi bi-person"></i> Профіль
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $activeTab === 'security' ? 'active' : ''; ?>" 
               href="?page=settings&tab=security" role="tab">
                <i class="bi bi-shield-lock"></i> Безпека
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $activeTab === 'integrations' ? 'active' : ''; ?>" 
               href="?page=settings&tab=integrations" role="tab">
                <i class="bi bi-plug"></i> Інтеграції
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $activeTab === 'role' ? 'active' : ''; ?>" 
               href="?page=settings&tab=role" role="tab">
                <i class="bi bi-person-badge"></i> Роль
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $activeTab === 'notifications' ? 'active' : ''; ?>" 
               href="?page=settings&tab=notifications" role="tab">
                <i class="bi bi-bell"></i> Сповіщення
            </a>
        </li>
    </ul>

    <!-- Вкладка: Профиль -->
    <?php if ($activeTab === 'profile'): ?>
    <div class="tab-pane fade show active">
        <div class="card border-0 shadow-sm">
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
    <?php endif; ?>

    <!-- Вкладка: Безпека -->
    <?php if ($activeTab === 'security'): ?>
    <div class="tab-pane fade show active">
        <div class="card border-0 shadow-sm">
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
    </div>
    <?php endif; ?>

    <!-- Вкладка: Интеграции -->
    <?php if ($activeTab === 'integrations'): ?>
    <div class="tab-pane fade show active">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3"><i class="bi bi-google text-primary"></i> Google Fit</h5>
                <p class="text-muted">Синхронізуйте свої дані про активність, пульс та інші показники з Google Fit</p>
                
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-3">
                    <div>
                        <h6 class="mb-1">Статус</h6>
                        <span class="badge bg-<?php echo $isGoogleFitConnected ? 'success' : 'danger'; ?>">
                            <?php echo $isGoogleFitConnected ? '✅ Підключено' : '❌ Не підключено'; ?>
                        </span>
                    </div>
                    <?php if ($isGoogleFitConnected): ?>
                        <button class="btn btn-outline-danger btn-sm" id="disconnectGoogleFitBtn">
                            <i class="bi bi-link"></i> Відключити
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary btn-sm" id="connectGoogleFitBtn">
                            <i class="bi bi-google"></i> Підключити
                        </button>
                    <?php endif; ?>
                </div>
                
                <?php if ($isGoogleFitConnected): ?>
                    <div class="mt-3">
                        <button class="btn btn-outline-primary btn-sm" id="syncGoogleFitBtn">
                            <i class="bi bi-arrow-repeat"></i> Синхронізувати дані
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Вкладка: Роль -->
    <?php if ($activeTab === 'role'): ?>
    <div class="tab-pane fade show active">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3"><i class="bi bi-person-badge text-primary"></i> Управління роллю</h5>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Ви зараз у ролі <strong>користувача</strong>. У вас є доступ до всіх функцій платформи.
                </div>
                
                <div class="mt-3">
                    <h6 class="fw-semibold text-primary">Стати тренером</h6>
                    <p class="text-muted small">
                        Ставши тренером, ви зможете:
                        <ul class="small text-muted">
                            <li>Керувати клієнтами</li>
                            <li>Створювати програми тренувань</li>
                            <li>Відстежувати прогрес клієнтів</li>
                            <li>Спілкуватися з клієнтами через чат</li>
                        </ul>
                    </p>
                    <button class="btn btn-primary" id="switchToTrainerBtn">
                        <i class="bi bi-arrow-right"></i> Стати тренером
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Вкладка: Сповіщення -->
    <?php if ($activeTab === 'notifications'): ?>
    <div class="tab-pane fade show active">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <h6 class="mb-0">Email сповіщення</h6>
                        <small class="text-muted">Отримувати сповіщення на пошту</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <h6 class="mb-0">Нагадування про тренування</h6>
                        <small class="text-muted">Отримувати нагадування за 1 годину до тренування</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="workoutReminders" checked>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <h6 class="mb-0">Досягнення</h6>
                        <small class="text-muted">Отримувати сповіщення про нові досягнення</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="achievementNotifications" checked>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div>
                        <h6 class="mb-0">Нові повідомлення</h6>
                        <small class="text-muted">Отримувати сповіщення про нові повідомлення</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="messageNotifications" checked>
                    </div>
                </div>
                
                <button class="btn btn-primary btn-gradient mt-3" onclick="saveNotificationSettings()">
                    <i class="bi bi-save"></i> Зберегти налаштування
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Индикатор силы пароля
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
    
    // Google Fit - подключение
    const connectBtn = document.getElementById('connectGoogleFitBtn');
    if (connectBtn) {
        connectBtn.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('action', 'auth_url');
            
            fetch('/api/google-fit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.url) {
                    window.location.href = data.url;
                } else {
                    showToast('Помилка отримання URL авторизації', 'danger');
                }
            })
            .catch(function() {
                showToast('Помилка з\'єднання', 'danger');
            });
        });
    }
    
    // Google Fit - отключение
    const disconnectBtn = document.getElementById('disconnectGoogleFitBtn');
    if (disconnectBtn) {
        disconnectBtn.addEventListener('click', function() {
            if (!confirm('Ви впевнені, що хочете відключити Google Fit?')) return;
            
            const formData = new FormData();
            formData.append('action', 'disconnect');
            
            fetch('/api/google-fit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Google Fit відключено', 'info');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Помилка відключення', 'danger');
                }
            })
            .catch(function() {
                showToast('Помилка з\'єднання', 'danger');
            });
        });
    }
    
    // Google Fit - синхронизация
    const syncBtn = document.getElementById('syncGoogleFitBtn');
    if (syncBtn) {
        syncBtn.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('action', 'sync');
            formData.append('days', 30);
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Синхронізація...';
            
            fetch('/api/google-fit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Синхронізувати дані';
                
                if (data.success) {
                    showToast('Дані успішно синхронізовано!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.error || 'Помилка синхронізації', 'danger');
                }
            })
            .catch(function() {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Синхронізувати дані';
                showToast('Помилка з\'єднання', 'danger');
            });
        });
    }
    
    // Переключение на тренера
    const switchToTrainerBtn = document.getElementById('switchToTrainerBtn');
    if (switchToTrainerBtn) {
        switchToTrainerBtn.addEventListener('click', function() {
            if (!confirm('Ви впевнені, що хочете стати тренером? Ви отримаєте доступ до функцій тренера.')) {
                return;
            }
            
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Зміна ролі...';
            
            const formData = new FormData();
            formData.append('action', 'switch_to_trainer');
            
            fetch('/api/trainer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Роль змінено на тренера! Сторінка оновиться...', 'success');
                    setTimeout(function() {
                        window.location.href = '/dashboard.php?page=settings&tab=role';
                    }, 1500);
                } else {
                    showToast(data.error || 'Помилка зміни ролі', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-right"></i> Стати тренером';
                }
            })
            .catch(function(error) {
                console.error('Ошибка:', error);
                showToast('Помилка з\'єднання з сервером', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-right"></i> Стати тренером';
            });
        });
    }
});

function saveNotificationSettings() {
    showToast('Налаштування збережено!', 'success');
}
</script>