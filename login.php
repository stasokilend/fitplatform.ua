<?php 
require_once 'includes/session.php';
require_once 'includes/Csrf.php';

if (isLoggedIn()) {
    require_once 'includes/auth.php';
    if (isProfileCompleted($_SESSION['user_id'])) {
        header('Location: ' . url('/dashboard.php'));
    } else {
        header('Location: ' . url('/profile-setup.php'));
    }
    exit;
}

$pageTitle = 'Вхід';
ob_start();
?>

<body class="auth-page">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <div class="bg-gradient-primary rounded-circle d-inline-flex p-3 mb-3" style="width: 64px; height: 64px; align-items: center; justify-content: center;">
                        <i class="bi bi-box-arrow-in-right text-white" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="card-title">Ласкаво просимо!</h3>
                    <p class="text-muted">Увійдіть у свій акаунт</p>
                </div>
                
                <form id="loginForm" action="<?= url('/controllers/AuthController.php'); ?>" method="POST">
                    <input type="hidden" name="action" value="login">
                    <?= csrfField(); ?>
                    
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email" id="loginEmail" name="email" class="form-control border-start-0" 
                                   placeholder="ivan@example.com" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label fw-semibold">Пароль</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-key text-muted"></i>
                            </span>
                            <input type="password" id="loginPassword" name="password" class="form-control border-start-0" 
                                   placeholder="Введіть пароль" required>
                            <button class="btn btn-outline-secondary toggle-password border-start-0" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-gradient py-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Увійти
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="text-muted small">
                        Ще не зареєстровані? 
                        <a href="<?= url('/register.php'); ?>" class="text-decoration-none fw-semibold" style="color: var(--primary);">
                            Створити акаунт <i class="bi bi-arrow-right"></i>
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