<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика – Адмін-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Навігація -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-heartbeat me-2"></i>FitPlatform</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Дашборд</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i> Користувачі</a></li>
                    <li class="nav-item"><a class="nav-link" href="exercises.php"><i class="fas fa-dumbbell"></i> Вправи</a></li>
                    <li class="nav-item"><a class="nav-link active" href="stats.php"><i class="fas fa-chart-pie"></i> Статистика</a></li>
                    <li class="nav-item"><a class="nav-link" href="../dashboard.php"><i class="fas fa-user"></i> Кабінет</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Вихід</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="mb-4"><i class="fas fa-chart-pie text-primary me-2"></i>Детальна статистика</h2>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white text-center p-3">
                    <h6>Користувачі</h6>
                    <h2 id="statTotalUsers">0</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white text-center p-3">
                    <h6>Тренери</h6>
                    <h2 id="statTrainers">0</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark text-center p-3">
                    <h6>Адміністратори</h6>
                    <h2 id="statAdmins">0</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white text-center p-3">
                    <h6>Всього тренувань</h6>
                    <h2 id="statTotalSessions">0</h2>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Розподіл ролей</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="rolesChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Активність (останні 7 днів)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="activityChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/config.js"></script>
    <script src="../js/auth.js"></script>
    <script src="js/stats.js"></script>
</body>
</html>