<?php
// login.php - Сторінка входу

$pageTitle = 'Вхід – FitPlatform';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-heartbeat fa-3x text-primary"></i>
                        <h2 class="mt-2">Ласкаво просимо!</h2>
                        <p class="text-muted">Увійдіть у свій акаунт</p>
                    </div>

                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <div id="loginError" class="alert alert-danger d-none"></div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">Увійти</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Немає акаунту? <a href="register.php">Зареєструватися</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>