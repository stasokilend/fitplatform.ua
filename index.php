<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitPlatform – Персоналізовані тренування</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (іконки) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Навігація -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-heartbeat me-2"></i>FitPlatform
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav"></div>
        </div>
    </nav>

    <!-- Hero-секція -->
    <header class="hero-section text-white text-center d-flex align-items-center">
        <div class="container">
            <h1 class="display-3 fw-bold">Ваш персональний фітнес-тренер</h1>
            <p class="lead mb-4">Адаптивні тренування на основі ваших цілей, стану здоров'я та даних у реальному часі</p>
            <a href="register.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-user-plus me-2"></i>Розпочати
            </a>
            <a href="login.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Увійти
            </a>
        </div>
    </header>

    <!-- Переваги -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Чому обирають FitPlatform?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Штучний інтелект</h5>
                            <p class="card-text">Алгоритми оптимізації створюють унікальний план вправ саме для вас.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-heartbeat fa-3x text-danger mb-3"></i>
                            <h5 class="card-title">Моніторинг здоров'я</h5>
                            <p class="card-text">Відстеження пульсу в реальному часі з рекомендаціями щодо інтенсивності.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Аналітика прогресу</h5>
                            <p class="card-text">Детальна статистика для відстеження ваших досягнень та мотивації.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Футер -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p class="mb-0">&copy; 2026 FitPlatform. Всі права захищено.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>