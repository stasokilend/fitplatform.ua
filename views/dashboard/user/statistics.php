<?php
require_once 'config/database.php';
require_once 'controllers/ProfileController.php';

$userId = $_SESSION['user_id'];
$profile = getUserProfile($userId);

// Получаем статистику тренировок за последние 7 дней
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count,
        SUM(total_duration_min) as total_duration,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
    FROM workout_plans
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$userId]);
$workoutStats = $stmt->fetchAll();

// Получаем упражнения (для статистики)
$stmt = $pdo->prepare("
    SELECT e.muscle_group, COUNT(pe.id) as count
    FROM plan_exercises pe
    JOIN exercises e ON pe.exercise_id = e.id
    JOIN workout_plans w ON pe.plan_id = w.id
    WHERE w.user_id = ?
    GROUP BY e.muscle_group
    ORDER BY count DESC
");
$stmt->execute([$userId]);
$muscleStats = $stmt->fetchAll();

// Общая статистика
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_workouts,
        SUM(total_duration_min) as total_minutes,
        AVG(total_duration_min) as avg_duration,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_workouts
    FROM workout_plans
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$totals = $stmt->fetch();

// Последние 5 тренировок
$stmt = $pdo->prepare("
    SELECT * FROM workout_plans
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentWorkouts = $stmt->fetchAll();
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-graph-up text-primary"></i> Статистика
        </h1>
        <div class="btn-group">
            <button class="btn btn-outline-secondary btn-sm active" data-period="30">
                <i class="bi bi-calendar3"></i> 30 днів
            </button>
            <button class="btn btn-outline-secondary btn-sm" data-period="90">
                <i class="bi bi-calendar3"></i> 90 днів
            </button>
            <button class="btn btn-outline-secondary btn-sm" data-period="365">
                <i class="bi bi-calendar3"></i> Рік
            </button>
        </div>
    </div>

    <!-- Общая статистика -->
    <div class="row g-4 mb-4">
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
                        <div class="stat-number text-warning"><?php echo round($totals['total_minutes'] ?? 0); ?></div>
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
                        <div class="stat-number text-info"><?php echo round($totals['avg_duration'] ?? 0); ?> хв</div>
                    </div>
                    <div class="stat-icon bg-info">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- График активности -->
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
    
    // Переключение периода
    document.querySelectorAll('[data-period]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-period]').forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            // Здесь можно добавить AJAX загрузку данных за новый период
            showToast('Завантаження даних за ' + this.dataset.period + ' днів...', 'info');
        });
    });
});
</script>