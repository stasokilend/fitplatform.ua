<?php 
require_once 'includes/session.php';
require_once 'includes/Csrf.php';

if (isLoggedIn()) {
    header('Location: ' . url('/dashboard.php'));
    exit;
}

$pageTitle = 'Реєстрація';
ob_start();
?>
<body class="auth-page">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <div class="bg-gradient-primary rounded-circle d-inline-flex p-3 mb-3" style="width: 64px; height: 64px; align-items: center; justify-content: center;">
                        <i class="bi bi-person-plus text-white" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="card-title">Приєднуйтесь!</h3>
                    <p class="text-muted">Створіть акаунт і почніть тренуватися</p>
                </div>
                
                <form id="registerForm" action="<?= url('/controllers/AuthController.php'); ?>" method="POST">
                    <input type="hidden" name="action" value="register">
                    <?= csrfField(); ?>
                    
                    <div class="mb-3">
                        <label for="fullName" class="form-label fw-semibold">Повне ім'я</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" id="fullName" name="full_name" class="form-control border-start-0" 
                                   placeholder="Іван Петренко" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email" id="email" name="email" class="form-control border-start-0" 
                                   placeholder="ivan@example.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Пароль</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-key text-muted"></i>
                            </span>
                            <input type="password" id="password" name="password" class="form-control border-start-0" 
                                   placeholder="Мінімум 6 символів" minlength="6" required>
                            <button class="btn btn-outline-secondary toggle-password border-start-0" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="progress" style="height: 4px;">
                                <div id="passwordStrength" class="progress-bar" style="width: 0%; transition: var(--transition);"></div>
                            </div>
                            <small class="text-muted" id="strengthText">Введіть пароль для оцінки надійності</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label fw-semibold">Підтвердіть пароль</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-check-circle text-muted"></i>
                            </span>
                            <input type="password" id="confirmPassword" name="confirm_password" class="form-control border-start-0" 
                                   placeholder="Повторіть пароль" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-gradient py-3">
                        <i class="bi bi-person-plus me-2"></i> Зареєструватися
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="text-muted small">
                        Вже маєте акаунт? 
                        <a href="<?= url('/login.php'); ?>" class="text-decoration-none fw-semibold" style="color: var(--primary);">
                            Увійти <i class="bi bi-arrow-right"></i>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<?php 
$content = ob_get_clean();
require_once 'views/layout.php';
?>