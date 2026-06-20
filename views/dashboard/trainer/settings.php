<?php
require_once 'config/database.php';
require_once 'controllers/TrainerController.php';
require_once 'controllers/GoogleFitController.php';

$userId = $_SESSION['user_id'];

// Проверяем актуальность роли
$stmt = $pdo->prepare("SELECT role, full_name FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

if ($userData) {
    if ($_SESSION['user_role'] !== $userData['role']) {
        $_SESSION['user_role'] = $userData['role'];
        $_SESSION['user_name'] = $userData['full_name'];
    }
}

$trainer = new TrainerController($userId);
$googleFit = new GoogleFitController($userId);
$isGoogleFitConnected = $googleFit->isConnected();

$activeTab = $_GET['tab'] ?? 'profile';
$message = '';

// Получаем данные тренера
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$trainerData = $stmt->fetch();

// Обработка обновления профиля тренера
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_trainer_profile') {
        $fullName = trim($_POST['full_name'] ?? $trainerData['full_name']);
        $specialty = trim($_POST['specialty'] ?? '');
        $experienceYears = (int)($_POST['experience_years'] ?? 0);
        $bio = trim($_POST['bio'] ?? '');
        
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, specialty = ?, experience_years = ?, bio = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$fullName, $specialty, $experienceYears, $bio, $userId])) {
            $_SESSION['user_name'] = $fullName;
            $message = ['type' => 'success', 'text' => 'Профіль оновлено!'];
            $trainerData = $stmt->fetch();
        } else {
            $message = ['type' => 'danger', 'text' => 'Помилка оновлення профілю'];
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
                    $message = ['type' => 'success', 'text' => 'Пароль змінено!'];
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

$clientsCount = $trainer->getActiveClientsCount();
$hasClients = $clientsCount > 0;
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-gear text-primary"></i> Налаштування тренера
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
    </ul>

    <!-- Вкладка: Профиль тренера -->
    <?php if ($activeTab === 'profile'): ?>
    <div class="tab-pane fade show active">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_trainer_profile">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Повне ім'я</label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($trainerData['full_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($trainerData['email'] ?? ''); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Спеціалізація</label>
                            <input type="text" name="specialty" class="form-control" 
                                   value="<?php echo htmlspecialchars($trainerData['specialty'] ?? ''); ?>" 
                                   placeholder="Наприклад: Силові тренування, функціональний фітнес">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Досвід (років)</label>
                            <input type="number" name="experience_years" class="form-control" 
                                   value="<?php echo $trainerData['experience_years'] ?? 0; ?>" 
                                   min="0" max="50">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Про себе</label>
                            <textarea name="bio" class="form-control" rows="4" 
                                      placeholder="Розкажіть про себе, свій досвід та підхід до тренувань"><?php echo htmlspecialchars($trainerData['bio'] ?? ''); ?></textarea>
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
                <h5 class="mb-3"><i class="bi bi-plug text-primary"></i> Google Fit</h5>
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
                    Ви зараз у ролі <strong>тренера</strong>. Ви маєте доступ до керування клієнтами та програмами.
                </div>
                
                <?php if ($hasClients): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        У вас є <strong><?php echo $clientsCount; ?></strong> активних клієнтів. 
                        Перед зміною ролі на користувача потрібно передати їх іншому тренеру.
                    </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <h6 class="fw-semibold text-danger">Змінити роль на користувача</h6>
                    <p class="text-muted small">
                        <?php if ($hasClients): ?>
                            <i class="bi bi-lock"></i> 
                            <span class="text-danger">Неможливо змінити роль, поки є активні клієнти.</span>
                            <br>Спочатку передайте всіх клієнтів іншому тренеру.
                        <?php else: ?>
                            Ви втратите доступ до функцій тренера. 
                            Ваші програми будуть заархівовані.
                        <?php endif; ?>
                    </p>
                    <button class="btn btn-danger <?php echo $hasClients ? 'disabled' : ''; ?>" 
                            id="switchToUserBtn"
                            <?php echo $hasClients ? 'title="Спочатку передайте клієнтів"' : ''; ?>>
                        <i class="bi bi-arrow-left"></i> Стати користувачем
                    </button>
                </div>
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
    
    // Переключение на пользователя
    const switchToUserBtn = document.getElementById('switchToUserBtn');
    if (switchToUserBtn) {
        switchToUserBtn.addEventListener('click', function() {
            if (!confirm('Ви впевнені, що хочете стати звичайним користувачем? Ви втратите доступ до функцій тренера.')) {
                return;
            }
            
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Зміна ролі...';
            
            const formData = new FormData();
            formData.append('action', 'switch_to_user');
            
            fetch('/api/trainer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Роль змінено на користувача! Сторінка оновиться...', 'success');
                    setTimeout(function() {
                        window.location.href = '/dashboard.php?page=settings&tab=role';
                    }, 1500);
                } else {
                    showToast(data.error || 'Помилка зміни ролі', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-left"></i> Стати користувачем';
                }
            })
            .catch(function(error) {
                console.error('Ошибка:', error);
                showToast('Помилка з\'єднання з сервером', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-left"></i> Стати користувачем';
            });
        });
    }
});
</script>