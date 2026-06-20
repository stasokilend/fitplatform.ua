<?php
require_once 'controllers/TrainerController.php';

$trainer = new TrainerController($userId);

// Получаем статистику
$stats = $trainer->getStats();

// Получаем всех клиентов для детальной статистики
$clients = $trainer->getClients('active');

// Статистика по тренировкам клиентов
$totalWorkouts = 0;
$completedWorkouts = 0;
$totalCalories = 0;
$totalMinutes = 0;

foreach ($clients as $client) {
    $progress = $trainer->getClientProgress($client['id']);
    $totalWorkouts += $progress['workout_stats']['total_workouts'] ?? 0;
    $completedWorkouts += $progress['workout_stats']['completed_workouts'] ?? 0;
    $totalCalories += $progress['workout_stats']['total_calories'] ?? 0;
    $totalMinutes += $progress['workout_stats']['total_minutes'] ?? 0;
}

// Статистика по уровням клиентов
$levelStats = [
    'beginner' => 0,
    'intermediate' => 0,
    'advanced' => 0
];

foreach ($clients as $client) {
    $level = $client['fitness_level'] ?? 'beginner';
    if (isset($levelStats[$level])) {
        $levelStats[$level]++;
    }
}

// Статистика по целям клиентов
$goalStats = [
    'weight_loss' => 0,
    'muscle_gain' => 0,
    'endurance' => 0,
    'health' => 0
];

foreach ($clients as $client) {
    $goal = $client['goal_type'] ?? 'health';
    if (isset($goalStats[$goal])) {
        $goalStats[$goal]++;
    }
}
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-graph-up text-primary"></i> Статистика тренера
        </h1>
        <span class="text-muted">
            <i class="bi bi-calendar"></i> <?php echo date('d.m.Y H:i'); ?>
        </span>
    </div>

    <!-- Общая статистика -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-primary mx-auto mb-2">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-number"><?php echo $stats['active_clients']; ?></div>
                <div class="stat-label">Клієнтів</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-success mx-auto mb-2">
                    <i class="bi bi-file-text"></i>
                </div>
                <div class="stat-number"><?php echo $stats['programs']; ?></div>
                <div class="stat-label">Програм</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-warning mx-auto mb-2">
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="stat-number"><?php echo $stats['unread_messages']; ?></div>
                <div class="stat-label">Непрочитаних</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-info mx-auto mb-2">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-number"><?php echo $stats['today_sessions']; ?></div>
                <div class="stat-label">Сьогодні</div>
            </div>
        </div>
    </div>

    <!-- Детальная статистика -->
    <div class="row g-4">
        <!-- Статистика клиентов -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-people text-primary"></i> Статистика клієнтів
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (count($clients) > 0): ?>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="display-6 text-primary"><?php echo $totalWorkouts; ?></div>
                                    <small class="text-muted">Всього тренувань</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="display-6 text-success"><?php echo $completedWorkouts; ?></div>
                                    <small class="text-muted">Завершено</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="display-6 text-warning"><?php echo number_format($totalMinutes, 0); ?></div>
                                    <small class="text-muted">Всього хвилин</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="display-6 text-danger"><?php echo number_format($totalCalories, 0); ?></div>
                                    <small class="text-muted">Калорій спалено</small>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-people display-4 d-block mb-2"></i>
                            <p>Немає клієнтів для статистики</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Распределение по уровням -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart text-primary"></i> Розподіл по рівнях
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (array_sum($levelStats) > 0): ?>
                        <div class="mb-3">
                            <?php 
                            $levelLabels = [
                                'beginner' => 'Початківці',
                                'intermediate' => 'Середній',
                                'advanced' => 'Просунуті'
                            ];
                            $levelColors = [
                                'beginner' => 'success',
                                'intermediate' => 'warning',
                                'advanced' => 'danger'
                            ];
                            foreach ($levelStats as $level => $count):
                                if ($count > 0):
                            ?>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><?php echo $levelLabels[$level]; ?></span>
                                    <span><?php echo $count; ?> клієнтів</span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-<?php echo $levelColors[$level]; ?>" 
                                         style="width: <?php echo ($count / array_sum($levelStats)) * 100; ?>%;">
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-bar-chart display-4 d-block mb-2"></i>
                            <p>Немає даних</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <!-- Распределение по целям -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart text-primary"></i> Цілі клієнтів
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (array_sum($goalStats) > 0): ?>
                        <div class="row g-2">
                            <?php 
                            $goalLabels = [
                                'weight_loss' => 'Зниження ваги',
                                'muscle_gain' => 'Набір маси',
                                'endurance' => 'Витривалість',
                                'health' => 'Здоров\'я'
                            ];
                            $goalColors = [
                                'weight_loss' => '#FF6B6B',
                                'muscle_gain' => '#6C63FF',
                                'endurance' => '#00D2A0',
                                'health' => '#FFB347'
                            ];
                            foreach ($goalStats as $goal => $count):
                                if ($count > 0):
                            ?>
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded-3 text-center">
                                        <div class="small text-muted"><?php echo $goalLabels[$goal]; ?></div>
                                        <div class="fw-bold"><?php echo $count; ?></div>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-pie-chart display-4 d-block mb-2"></i>
                            <p>Немає даних</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Последние клиенты -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-primary"></i> Останні клієнти
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($clients) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php 
                            $recentClients = array_slice($clients, 0, 5);
                            foreach ($recentClients as $client): 
                            ?>
                                <a href="/dashboard.php?page=client-detail&id=<?php echo $client['id']; ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($client['full_name']); ?></div>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> 
                                            <?php echo date('d.m.Y', strtotime($client['assigned_at'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $client['fitness_level'] === 'advanced' ? 'danger' : ($client['fitness_level'] === 'intermediate' ? 'warning' : 'success'); ?>">
                                        <?php echo getFitnessLevelLabel($client['fitness_level']); ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-people display-4 d-block mb-2"></i>
                            <p>Немає клієнтів</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>