<?php
// register.php - Сторінка реєстрації

$pageTitle = 'Реєстрація – FitPlatform';
$extraScripts = '<script src="js/auth.js"></script>';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-dumbbell fa-3x text-primary"></i>
                        <h2 class="mt-2">Реєстрація</h2>
                        <p class="text-muted">Створіть свій обліковий запис</p>
                    </div>

                    <form id="registerForm">
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Повне ім'я</label>
                            <input type="text" class="form-control" id="fullName" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" class="form-control" id="password" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Підтвердження пароля</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                        <div id="registerError" class="alert alert-danger d-none"></div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">Зареєструватися</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Вже маєте акаунт? <a href="login.php">Увійдіть</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>