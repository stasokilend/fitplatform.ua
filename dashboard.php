<?php
// dashboard.php - Кабінет користувача

$pageTitle = 'Кабінет – FitPlatform';
$extraStyles = '
<style>
    .avatar-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: bold;
        color: white;
    }
    .stat-mini-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
        transition: none;
    }
    .stat-mini-card .number {
        font-size: 1.8rem;
        font-weight: bold;
    }
    .stat-mini-card .label {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .workout-list-item {
        border-left: 4px solid #0d6efd;
        padding-left: 15px;
        margin-bottom: 10px;
    }
    .workout-list-item .date {
        font-size: 0.8rem;
        color: #6c757d;
    }
</style>';

$extraScripts = '
<script src="js/profile.js"></script>
<script src="js/workout.js"></script>
<script src="js/dashboard.js"></script>
';

// Підключаємо хедер
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Профіль та привітання -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div id="avatarContainer" class="me-3">
                    <div class="avatar-circle bg-primary" id="avatarDisplay">U</div>
                </div>
                <div>
                    <h2 id="welcomeMessage" class="mb-0">Вітаємо, <span id="userName">Користувач</span>!</h2>
                    <p class="text-muted" id="userEmail">email@example.com</p>
                    <div class="mt-1">
                        <span class="badge bg-secondary" id="userLevel">Початківець</span>
                        <span class="badge bg-info" id="userBmr">BMR: 0 ккал</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="profile-setup.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-edit me-1"></i>Редагувати профіль
            </a>
            <a href="settings.php" class="btn btn-outline-secondary btn-sm ms-1">
                <i class="fas fa-cog"></i>
            </a>
        </div>
    </div>

    <!-- Міні-статистика -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-mini-card">
                <div class="number" id="miniSessions">0</div>
                <div class="label">Тренувань</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini-card">
                <div class="number" id="miniMinutes">0</div>
                <div class="label">Хвилин</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini-card">
                <div class="number" id="miniCalories">0</div>
                <div class="label">Калорій</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini-card">
                <div class="number" id="miniStreak">0</div>
                <div class="label">Днів поспіль</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Ліва колонка: Генерація та поточний план -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-play me-2"></i>Тренування</h5>
                </div>
                <div class="card-body text-center">
                    <button id="generateWorkoutBtn" class="btn btn-success btn-lg w-100 mb-2">
                        <i class="fas fa-sync me-2"></i>Згенерувати план
                    </button>
                    <div id="generationStatus" class="small text-muted"></div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Поточний план</h5>
                    <span id="planDate" class="small"></span>
                </div>
                <div class="card-body" id="currentPlanContainer">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-dumbbell fa-3x mb-3"></i>
                        <p>Натисніть "Згенерувати план", щоб створити персоналізоване тренування</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Права колонка: Прогрес та останні тренування -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Прогрес за тиждень</h5>
                </div>
                <div class="card-body">
                    <canvas id="progressChart" height="200"></canvas>
                    <div id="noProgressData" class="text-center text-muted py-3 d-none">
                        <p>Немає даних за останній тиждень</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Останні тренування</h5>
                    <a href="stats.php" class="text-white text-decoration-none small">Всі →</a>
                </div>
                <div class="card-body" id="recentWorkouts">
                    <p class="text-muted text-center py-3">Немає тренувань. Почніть займатися!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Підключаємо футер
require_once __DIR__ . '/includes/footer.php';
?>