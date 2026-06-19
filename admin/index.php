<?php
// admin/index.php - Адмін-дашборд

$pageTitle = 'Адмін-панель – FitPlatform';
$extraStyles = '
<style>
    .stat-card {
        border-radius: 15px;
        padding: 20px;
        color: white;
    }
    .stat-card i {
        font-size: 2.5rem;
        opacity: 0.7;
    }
    .stat-card .number {
        font-size: 2.5rem;
        font-weight: bold;
    }
    .stat-card .label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
</style>';

$extraScripts = '<script src="../js/dashboard.js"></script>';

// В адмінці шляхи трохи інші
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-tachometer-alt text-primary me-2"></i>Адмін-панель</h2>

    <!-- Статистика -->
    <div class="row g-3 mb-4" id="statsCards">
        <div class="col-md-3">
            <div class="stat-card bg-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="number" id="statUsers">0</div>
                        <div class="label">Користувачів</div>
                    </div>
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="number" id="statExercises">0</div>
                        <div class="label">Вправ</div>
                    </div>
                    <i class="fas fa-dumbbell"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-warning text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="number" id="statSessions">0</div>
                        <div class="label">Тренувань</div>
                    </div>
                    <i class="fas fa-running"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="number" id="statMinutes">0</div>
                        <div class="label">Хвилин</div>
                    </div>
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Останні користувачі та тренування -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Нові користувачі</h5>
                </div>
                <div class="card-body" id="recentUsers">
                    <p class="text-muted">Завантаження...</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Останні тренування</h5>
                </div>
                <div class="card-body" id="recentSessions">
                    <p class="text-muted">Завантаження...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>