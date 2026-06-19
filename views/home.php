<?php 
$pageTitle = 'Головна';
ob_start();
?>

<div class="container py-5">
    <div class="row align-items-center min-vh-75">
        <div class="col-lg-6">
            <h1 class="display-4 fw-bold text-primary">
                <i class="bi bi-heart-pulse-fill text-danger"></i> FitPlatform
            </h1>
            <p class="lead text-muted mt-3">
                Персоналізовані фітнес-тренування та моніторинг здоров'я
            </p>
            <p class="text-secondary">
                Досягайте своїх цілей з індивідуальними планами тренувань,
                відстеженням прогресу та підтримкою професійних тренерів.
            </p>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="mt-4">
                    <a href="/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-speedometer2"></i> Перейти до кабінету
                    </a>
                </div>
                <div class="mt-3">
                    <small class="text-success">
                        <i class="bi bi-check-circle-fill"></i> 
                        Ви вже в системі, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Користувач'); ?>
                    </small>
                </div>
            <?php else: ?>
                <div class="mt-4 d-flex gap-3 flex-wrap">
                    <a href="/register.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Реєстрація
                    </a>
                    <a href="/login.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Вхід
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-6 text-center d-none d-lg-block">
            <img src="https://placehold.co/500x400/0d6efd/fff?text=FitPlatform" 
                 alt="FitPlatform" class="img-fluid rounded shadow">
        </div>
    </div>
    
    <!-- Краткие преимущества -->
    <div class="row mt-5 pt-5 g-4">
        <div class="col-md-4 text-center">
            <div class="display-4 text-primary"><i class="bi bi-person-gear"></i></div>
            <h5>Персоналізація</h5>
            <p class="text-muted small">Тренування адаптовані під ваші цілі та стан здоров'я</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="display-4 text-success"><i class="bi bi-graph-up-arrow"></i></div>
            <h5>Прогрес</h5>
            <p class="text-muted small">Відстежуйте свої досягнення в реальному часі</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="display-4 text-danger"><i class="bi bi-heart-pulse"></i></div>
            <h5>Безпека</h5>
            <p class="text-muted small">Моніторинг стану та контроль навантаження</p>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once 'layout.php';
?>