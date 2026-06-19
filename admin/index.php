<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/includes/admin-functions.php';

requireAdminLogin();

$stats = getAdminStats();
$recentActivity = getRecentActivity(10);
$monthlyStats = getMonthlyStats(6);

$pageTitle = 'Головна';
ob_start();
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2"><i class="bi bi-speedometer2"></i> Панель керування</h1>
        <div>
            <span class="text-muted">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
            </span>
            <a href="/admin/logout.php" class="btn btn-sm btn-outline-danger ms-2">
                <i class="bi bi-box-arrow-right"></i> Вихід
            </a>
        </div>
    </div>
    
    <!-- Статистика -->
    <div class="row g-4 mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title text-white-50"><i class="bi bi-people"></i> Користувачів</h6>
                    <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                    <small>+<?php echo $stats['new_today']; ?> сьогодні</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title text-white-50"><i class="bi bi-person-check"></i> Активних</h6>
                    <h2 class="mb-0"><?php echo $stats['active_users']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title text-dark-50"><i class="bi bi-person-badge"></i> Тренерів</h6>
                    <h2 class="mb-0"><?php echo $stats['total_trainers']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title text-white-50"><i class="bi bi-dumbbell"></i> Вправ</h6>
                    <h2 class="mb-0"><?php echo $stats['total_exercises']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6 class="card-title text-white-50"><i class="bi bi-calendar-check"></i> Тренувань</h6>
                    <h2 class="mb-0"><?php echo $stats['total_workouts']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h6 class="card-title text-white-50"><i class="bi bi-check-circle"></i> Завершено</h6>
                    <h2 class="mb-0"><?php echo $stats['completed_workouts']; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- График активности -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-graph-up"></i> Активність за останні 6 місяців
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Быстрые действия -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning"></i> Швидкі дії
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/admin/users.php" class="btn btn-outline-primary">
                            <i class="bi bi-people"></i> Керувати користувачами
                        </a>
                        <a href="/admin/exercises.php" class="btn btn-outline-success">
                            <i class="bi bi-dumbbell"></i> Керувати вправами
                        </a>
                        <a href="/admin/restrictions.php" class="btn btn-outline-warning">
                            <i class="bi bi-heart-pulse"></i> Медичні обмеження
                        </a>
                        <a href="/admin/workouts.php" class="btn btn-outline-info">
                            <i class="bi bi-calendar-check"></i> Тренування
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <!-- Недавняя активность -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> Остання активність
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (count($recentActivity) > 0): ?>
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($activity['type'] === 'registration'): ?>
                                                <i class="bi bi-person-plus text-success"></i>
                                                <strong><?php echo htmlspecialchars($activity['full_name']); ?></strong>
                                                <span class="text-muted">зареєструвався</span>
                                            <?php elseif ($activity['type'] === 'workout'): ?>
                                                <i class="bi bi-calendar-check text-primary"></i>
                                                <strong><?php echo htmlspecialchars($activity['full_name']); ?></strong>
                                                <span class="text-muted">створив тренування</span>
                                            <?php else: ?>
                                                <i class="bi bi-activity text-info"></i>
                                                <strong><?php echo htmlspecialchars($activity['full_name']); ?></strong>
                                                <span class="text-muted">займався</span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('d.m.Y H:i', strtotime($activity['time'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                Немає активності
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (count($monthlyStats) > 0): ?>
        var ctx = document.getElementById('activityChart').getContext('2d');
        var monthlyData = <?php echo json_encode($monthlyStats); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.map(function(item) {
                    var parts = item.month.split('-');
                    return parts[1] + '/' + parts[0];
                }),
                datasets: [
                    {
                        label: 'Нові користувачі',
                        data: monthlyData.map(function(item) { return item.new_users; }),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Нові тренування',
                        data: monthlyData.map(function(item) { return item.new_workouts; }),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
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
});
</script>

<?php 
$content = ob_get_clean();
require_once 'layout/admin-layout.php';
?>