<?php
require_once 'config/database.php';
require_once 'controllers/ProfileController.php';

$userId = $_SESSION['user_id'];
$profile = getUserProfile($userId);

// Період статистики: 30 / 90 / 365 днів
$allowedPeriods = [30, 90, 365];
$selectedPeriod = (int)($_GET['period'] ?? 30);
if (!in_array($selectedPeriod, $allowedPeriods, true)) {
    $selectedPeriod = 30;
}

function formatStatNumber($value, $decimals = 0) {
    return number_format((float)$value, $decimals, '.', ' ');
}

// Динаміка тренувань за вибраний період
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count,
        SUM(total_duration_min) as total_duration,
        SUM(calories_burned) as calories,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
    FROM workout_plans
    WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$userId, $selectedPeriod]);
$workoutStats = $stmt->fetchAll();

// Розподіл вправ по групах м'язів за вибраний період
$stmt = $pdo->prepare("
    SELECT e.muscle_group, COUNT(pe.id) as count
    FROM plan_exercises pe
    JOIN exercises e ON pe.exercise_id = e.id
    JOIN workout_plans w ON pe.plan_id = w.id
    WHERE w.user_id = ? AND w.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
    GROUP BY e.muscle_group
    ORDER BY count DESC
");
$stmt->execute([$userId, $selectedPeriod]);
$muscleStats = $stmt->fetchAll();

// Загальна статистика за вибраний період
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_workouts,
        COALESCE(SUM(total_duration_min), 0) as total_minutes,
        COALESCE(AVG(total_duration_min), 0) as avg_duration,
        COALESCE(SUM(calories_burned), 0) as total_calories,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_workouts,
        COUNT(DISTINCT DATE(created_at)) as active_days
    FROM workout_plans
    WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
");
$stmt->execute([$userId, $selectedPeriod]);
$totals = $stmt->fetch();

// Статистика за весь час для особистих рекордів
$stmt = $pdo->prepare("
    SELECT
        COALESCE(MAX(total_duration_min), 0) as longest_workout,
        COALESCE(MAX(calories_burned), 0) as best_calories,
        COUNT(CASE WHEN status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as completed_week
    FROM workout_plans
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$records = $stmt->fetch();

// Гейміфікація для серії тренувань
$stmt = $pdo->prepare("SELECT current_streak, max_streak FROM user_gamification_stats WHERE user_id = ?");
$stmt->execute([$userId]);
$streakStats = $stmt->fetch() ?: ['current_streak' => 0, 'max_streak' => 0];

// Останні 5 тренувань
$stmt = $pdo->prepare("
    SELECT * FROM workout_plans
    WHERE user_id = ?
    ORDER BY COALESCE(completed_at, created_at) DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentWorkouts = $stmt->fetchAll();

$completionRate = (int)($totals['total_workouts'] ?? 0) > 0
    ? round(((int)$totals['completed_workouts'] / (int)$totals['total_workouts']) * 100)
    : 0;
$avgPerWeek = $selectedPeriod > 0 ? round(((int)($totals['total_workouts'] ?? 0) / $selectedPeriod) * 7, 1) : 0;
$minutesHours = round(((float)($totals['total_minutes'] ?? 0)) / 60, 1);
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-graph-up text-primary"></i> Статистика
        </h1>
        <div class="btn-group">
            <?php foreach ([30 => '30 днів', 90 => '90 днів', 365 => 'Рік'] as $period => $label): ?>
                <a class="btn btn-outline-secondary btn-sm <?php echo $selectedPeriod === $period ? 'active' : ''; ?>" href="/dashboard.php?page=statistics&period=<?php echo $period; ?>">
                    <i class="bi bi-calendar3"></i> <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Загальна статистика -->
    <div class="stats-hero card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge bg-primary-subtle text-primary mb-2">Період: <?php echo $selectedPeriod; ?> днів</span>
                    <h4 class="mb-2">Ваш тренувальний прогрес</h4>
                    <p class="text-muted mb-3">Завершено <?php echo $completionRate; ?>% тренувань, активних днів — <?php echo (int)($totals['active_days'] ?? 0); ?>, середній темп — <?php echo $avgPerWeek; ?> трен./тиждень.</p>
                    <div class="progress stats-progress" role="progressbar" aria-valuenow="<?php echo $completionRate; ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-gradient-primary" style="width: <?php echo $completionRate; ?>%;"></div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row g-3 text-center">
                        <div class="col-4"><div class="mini-metric"><strong><?php echo formatStatNumber($minutesHours, 1); ?></strong><span>годин</span></div></div>
                        <div class="col-4"><div class="mini-metric"><strong><?php echo (int)($streakStats['current_streak'] ?? 0); ?></strong><span>серія</span></div></div>
                        <div class="col-4"><div class="mini-metric"><strong><?php echo (int)($records['completed_week'] ?? 0); ?></strong><span>за 7 днів</span></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 dashboard-stats">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Всього тренувань</div>
                        <div class="stat-number"><?php echo $totals['total_workouts'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon bg-primary">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Завершено</div>
                        <div class="stat-number text-success"><?php echo $totals['completed_workouts'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon bg-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Всього хвилин</div>
                        <div class="stat-number text-warning"><?php echo formatStatNumber($totals['total_minutes'] ?? 0); ?></div>
                    </div>
                    <div class="stat-icon bg-warning">
                        <i class="bi bi-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Середня тривалість</div>
                        <div class="stat-number text-info"><?php echo formatStatNumber($totals['avg_duration'] ?? 0); ?> хв</div>
                    </div>
                    <div class="stat-icon bg-info">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 dashboard-stats">
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Калорій</div><div class="stat-number text-danger"><?php echo formatStatNumber($totals['total_calories'] ?? 0); ?></div><small class="text-muted"><i class="bi bi-fire"></i> за період</small></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Активних днів</div><div class="stat-number text-primary"><?php echo (int)($totals['active_days'] ?? 0); ?></div><small class="text-muted"><i class="bi bi-calendar-week"></i> регулярність</small></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Найдовше тренування</div><div class="stat-number text-success"><?php echo formatStatNumber($records['longest_workout'] ?? 0); ?> хв</div><small class="text-muted"><i class="bi bi-trophy"></i> рекорд</small></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Макс. серія</div><div class="stat-number text-warning"><?php echo (int)($streakStats['max_streak'] ?? 0); ?></div><small class="text-muted"><i class="bi bi-lightning-charge"></i> днів поспіль</small></div></div>
    </div>

    <!-- Графік активності -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bi bi-graph-up-arrow text-primary"></i> Динаміка тренувань</h5>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bi bi-pie-chart text-primary"></i> Розподіл по групах м'язів</h5>
                </div>
                <div class="card-body">
                    <canvas id="muscleChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние тренировки -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0"><i class="bi bi-clock-history text-primary"></i> Останні тренування</h5>
        </div>
        <div class="card-body p-0">
            <?php if (count($recentWorkouts) > 0): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentWorkouts as $workout): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($workout['name']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> <?php echo date('d.m.Y H:i', strtotime($workout['created_at'])); ?>
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-clock"></i> <?php echo $workout['total_duration_min']; ?> хв
                                </small>
                            </div>
                            <span class="badge bg-<?php echo $workout['status'] === 'completed' ? 'success' : 'warning'; ?> rounded-pill">
                                <?php echo $workout['status'] === 'completed' ? '✅ Завершено' : '⏳ В процесі'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                    <p>Ще немає тренувань для статистики</p>
                    <a href="/dashboard.php?page=workouts" class="btn btn-primary btn-gradient">
                        <i class="bi bi-plus-circle"></i> Створити тренування
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // График активности
    <?php if (count($workoutStats) > 0): ?>
        var ctx1 = document.getElementById('activityChart').getContext('2d');
        var workoutData = <?php echo json_encode($workoutStats); ?>;
        
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: workoutData.map(function(item) {
                    var parts = item.date.split('-');
                    return parts[2] + '/' + parts[1];
                }),
                datasets: [
                    {
                        label: 'Тренування',
                        data: workoutData.map(function(item) { return item.count; }),
                        borderColor: '#6C63FF',
                        backgroundColor: 'rgba(108, 99, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Завершено',
                        data: workoutData.map(function(item) { return item.completed; }),
                        borderColor: '#00D2A0',
                        backgroundColor: 'rgba(0, 210, 160, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    <?php endif; ?>
    
    // График мышц
    <?php if (count($muscleStats) > 0): ?>
        var ctx2 = document.getElementById('muscleChart').getContext('2d');
        var muscleData = <?php echo json_encode($muscleStats); ?>;
        
        var colors = ['#6C63FF', '#00D2A0', '#FF6584', '#FFB347', '#4ECDC4', '#45B7D1'];
        
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: muscleData.map(function(item) { return item.muscle_group; }),
                datasets: [{
                    data: muscleData.map(function(item) { return item.count; }),
                    backgroundColor: colors.slice(0, muscleData.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    <?php endif; ?>
    
});
</script>