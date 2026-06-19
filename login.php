<?php 
// Запускаем сессию в самом начале
require_once 'includes/session.php';

// Если пользователь уже авторизован - перенаправляем в кабинет
if (isLoggedIn()) {
    // Проверяем заполнен ли профиль
    require_once 'includes/auth.php';
    if (isProfileCompleted($_SESSION['user_id'])) {
        header('Location: /dashboard.php');
    } else {
        header('Location: /profile-setup.php');
    }
    exit;
}

$pageTitle = 'Вхід';
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">
                        <i class="bi bi-box-arrow-in-right text-primary"></i> Вхід
                    </h3>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                            echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            echo htmlspecialchars($_SESSION['success']);
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form id="loginForm" action="/controllers/AuthController.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email</label>
                            <input type="email" id="loginEmail" name="email" class="form-control" 
                                   placeholder="ivan@example.com" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Пароль</label>
                            <div class="input-group">
                                <input type="password" id="loginPassword" name="password" class="form-control" 
                                       placeholder="Введіть пароль" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Увійти
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small>
                            Ще не зареєстровані? 
                            <a href="/register.php" class="text-decoration-none">Створити акаунт</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once 'views/layout.php';
?>