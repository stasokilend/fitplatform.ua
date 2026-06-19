<?php 
require_once 'includes/session.php';
$pageTitle = 'Реєстрація';
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">
                        <i class="bi bi-person-plus text-primary"></i> Реєстрація
                    </h3>
                    
                    <form action="/controllers/AuthController.php" method="POST">
                        <input type="hidden" name="action" value="register">
                        
                        <div class="mb-3">
                            <label class="form-label">Повне ім'я</label>
                            <input type="text" name="full_name" class="form-control" 
                                   placeholder="Іван Петренко" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   placeholder="ivan@example.com" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Мінімум 6 символів" minlength="6" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus"></i> Зареєструватися
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small>
                            Вже маєте акаунт? 
                            <a href="/login.php" class="text-decoration-none">Увійти</a>
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