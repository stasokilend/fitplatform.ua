<?php
require_once 'config/database.php';
require_once 'controllers/GamificationController.php';

$userId = $_SESSION['user_id'];
$gamification = new GamificationController($userId);
$achievements = $gamification->getUserAchievements();
$stats = $gamification->getGamificationStats();

// Группировка по категориям
$categories = [
    'workout' => ['name' => 'Тренування', 'icon' => 'bi-calendar-check'],
    'streak' => ['name' => 'Серії', 'icon' => 'bi-calendar2-range'],
    'strength' => ['name' => 'Сила', 'icon' => 'bi-dumbbell'],
    'special' => ['name' => 'Спеціальні', 'icon' => 'bi-award']
];

$grouped = [];
foreach ($achievements as $ach) {
    $cat = $ach['category'];
    if (!isset($grouped[$cat])) {
        $grouped[$cat] = [];
    }
    $grouped[$cat][] = $ach;
}
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-trophy text-warning"></i> Достигнення
        </h1>
        <div>
            <span class="text-muted me-3">
                <i class="bi bi-star"></i> 
                <?php echo $stats['completed_achievements']; ?>/<?php echo $stats['total_achievements']; ?>
            </span>
            <span class="badge bg-primary">
                <i class="bi bi-coin"></i> <?php echo $stats['total_points']; ?> балів
            </span>
        </div>
    </div>

    <!-- Прогресс -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <div class="display-4 text-warning">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h4 class="mb-0">Рівень <?php echo $stats['level']; ?></h4>
                    <small class="text-muted"><?php echo $stats['total_experience']; ?> досвіду</small>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between small">
                        <span>Прогрес</span>
                        <span><?php echo round($stats['completion_percent']); ?>%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: <?php echo $stats['completion_percent']; ?>%;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between small mt-1">
                        <span>Досвід: <?php echo $stats['experience']; ?></span>
                        <span>До наступного рівня: <?php echo $stats['next_level']; ?></span>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="row">
                        <div class="col-6">
                            <div class="badge bg-danger p-2">🔥 <?php echo $stats['streak']; ?></div>
                            <small class="d-block text-muted">Серія</small>
                        </div>
                        <div class="col-6">
                            <div class="badge bg-success p-2">💪 <?php echo $stats['total_workouts']; ?></div>
                            <small class="d-block text-muted">Тренувань</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Достижения по категориям -->
    <?php foreach ($categories as $catKey => $catInfo): ?>
        <?php if (isset($grouped[$catKey])): ?>
            <div class="mb-4">
                <h5 class="mb-3">
                    <i class="bi <?php echo $catInfo['icon']; ?> text-primary"></i>
                    <?php echo $catInfo['name']; ?>
                </h5>
                <div class="row g-3">
                    <?php foreach ($grouped[$catKey] as $ach): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card achievement-card <?php echo $ach['is_completed'] ? 'completed' : 'locked'; ?> border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="achievement-icon mb-2" style="color: <?php echo $ach['is_completed'] ? '#FFD700' : '#6C757D'; ?>;">
                                        <i class="bi <?php echo $ach['icon']; ?> display-5"></i>
                                    </div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($ach['name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($ach['description']); ?></small>
                                    <div class="mt-2">
                                        <?php if ($ach['is_completed']): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Розблоковано
                                            </span>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-coin"></i> <?php echo $ach['points']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-lock"></i> Заблоковано
                                            </span>
                                            <span class="badge bg-light text-dark">
                                                Прогрес: <?php echo min($ach['progress'], $ach['requirement_value']); ?>/<?php echo $ach['requirement_value']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!$ach['is_completed']): ?>
                                        <div class="mt-2">
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-primary" 
                                                     style="width: <?php echo min(($ach['progress'] / $ach['requirement_value']) * 100, 100); ?>%;">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($ach['is_completed'] && $ach['unlocked_at']): ?>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <?php echo date('d.m.Y', strtotime($ach['unlocked_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<style>
.achievement-card {
    transition: all 0.3s ease;
    cursor: default;
}

.achievement-card.completed {
    border-left: 4px solid #FFD700 !important;
}

.achievement-card.locked {
    opacity: 0.7;
}

.achievement-card.locked .achievement-icon {
    filter: grayscale(1);
}

.achievement-card .badge {
    font-size: 0.7rem;
}

.achievement-card .progress {
    background-color: #e9ecef;
}

.achievement-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover, 0 4px 15px rgba(0,0,0,0.1)) !important;
}
</style>