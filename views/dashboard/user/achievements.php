<?php
require_once 'config/database.php';
require_once 'controllers/GamificationController.php';

$userId = $_SESSION['user_id'];
$gamification = new GamificationController($userId);
$achievements = $gamification->getUserAchievements();
$stats = $gamification->getGamificationStats();
$summary = $gamification->getAchievementSummary();
$recent = $gamification->getRecentAchievements(5);

// Группировка по категориям
$categories = [
    'workout' => ['name' => 'Тренування', 'icon' => 'bi-calendar-check', 'color' => '#6C63FF'],
    'streak' => ['name' => 'Серії', 'icon' => 'bi-calendar2-range', 'color' => '#FFB347'],
    'strength' => ['name' => 'Сила', 'icon' => 'bi-dumbbell', 'color' => '#FF6584'],
    'special' => ['name' => 'Спеціальні', 'icon' => 'bi-award', 'color' => '#00D2A0']
];

$grouped = [];
foreach ($achievements as $ach) {
    $cat = $ach['category'] ?? 'other';
    if (!isset($grouped[$cat])) {
        $grouped[$cat] = [];
    }
    $grouped[$cat][] = $ach;
}

// Редкость
$rarityLabels = [
    'common' => 'Звичайний',
    'uncommon' => 'Нечастий',
    'rare' => 'Рідкісний',
    'epic' => 'Епічний',
    'legendary' => 'Легендарний'
];

$rarityColors = [
    'common' => '#8B8B8B',
    'uncommon' => '#2ECC71',
    'rare' => '#3498DB',
    'epic' => '#9B59B6',
    'legendary' => '#F39C12'
];

$rarityBg = [
    'common' => 'bg-secondary',
    'uncommon' => 'bg-success',
    'rare' => 'bg-info',
    'epic' => 'bg-purple',
    'legendary' => 'bg-warning'
];
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-trophy text-warning"></i> Досягнення
        </h1>
        <div class="d-flex gap-2">
            <span class="badge bg-primary fs-6 px-3 py-2">
                <i class="bi bi-coin"></i> <?php echo $stats['total_points']; ?> балів
            </span>
            <span class="badge bg-success fs-6 px-3 py-2">
                <i class="bi bi-check-circle"></i> <?php echo $stats['completed_achievements']; ?>/<?php echo $stats['total_achievements']; ?>
            </span>
        </div>
    </div>

    <!-- Прогресс-бар -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-3 text-center">
                    <div class="position-relative d-inline-block">
                        <div class="display-1 text-warning">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div class="position-absolute top-0 end-0 translate-middle badge bg-primary rounded-pill fs-6">
                            <?php echo $stats['level']; ?>
                        </div>
                    </div>
                    <h5 class="mb-0 mt-2">Рівень <?php echo $stats['level']; ?></h5>
                    <small class="text-muted"><?php echo number_format($stats['total_experience']); ?> досвіду</small>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex justify-content-between small">
                        <span>Прогрес досягнень</span>
                        <span><?php echo $summary['completion_percent']; ?>%</span>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar bg-warning" style="width: <?php echo $summary['completion_percent']; ?>%;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between small mt-1">
                        <span>Досвід: <?php echo $stats['experience']; ?></span>
                        <span>До наступного рівня: <?php echo $stats['next_level']; ?></span>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="d-flex justify-content-around">
                        <div class="text-center">
                            <div class="fw-bold text-success"><?php echo $summary['by_rarity']['common']['completed']; ?>/<?php echo $summary['by_rarity']['common']['total']; ?></div>
                            <small class="text-muted">Звич.</small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-info"><?php echo $summary['by_rarity']['uncommon']['completed']; ?>/<?php echo $summary['by_rarity']['uncommon']['total']; ?></div>
                            <small class="text-muted">Нечаст.</small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-primary"><?php echo $summary['by_rarity']['rare']['completed']; ?>/<?php echo $summary['by_rarity']['rare']['total']; ?></div>
                            <small class="text-muted">Рідк.</small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-purple"><?php echo $summary['by_rarity']['epic']['completed']; ?>/<?php echo $summary['by_rarity']['epic']['total']; ?></div>
                            <small class="text-muted">Епіч.</small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-warning"><?php echo $summary['by_rarity']['legendary']['completed']; ?>/<?php echo $summary['by_rarity']['legendary']['total']; ?></div>
                            <small class="text-muted">Легенд.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние достижения -->
    <?php if (count($recent) > 0): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0">
                <i class="bi bi-clock-history text-primary"></i> Останні досягнення
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3">
                <?php foreach ($recent as $ach): ?>
                    <div class="d-flex align-items-center bg-light rounded-3 p-2">
                        <div class="me-2" style="color: <?php echo $rarityColors[$ach['rarity']] ?? '#8B8B8B'; ?>;">
                            <i class="bi <?php echo $ach['icon']; ?> fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small"><?php echo htmlspecialchars($ach['name']); ?></div>
                            <small class="text-muted"><?php echo date('d.m.Y', strtotime($ach['unlocked_at'])); ?></small>
                        </div>
                        <span class="badge <?php echo $rarityBg[$ach['rarity']] ?? 'bg-secondary'; ?> ms-2">
                            <?php echo $rarityLabels[$ach['rarity']] ?? 'Звичайний'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Достижения по категориям -->
    <?php foreach ($categories as $catKey => $catInfo): ?>
        <?php if (isset($grouped[$catKey])): ?>
            <div class="mb-4">
                <h5 class="mb-3">
                    <i class="bi <?php echo $catInfo['icon']; ?>" style="color: <?php echo $catInfo['color']; ?>;"></i>
                    <?php echo $catInfo['name']; ?>
                    <span class="badge bg-secondary ms-2">
                        <?php 
                        $total = count($grouped[$catKey]);
                        $completed = 0;
                        foreach ($grouped[$catKey] as $ach) {
                            if ($ach['is_completed']) $completed++;
                        }
                        echo $completed . '/' . $total;
                        ?>
                    </span>
                </h5>
                <div class="row g-3">
                    <?php foreach ($grouped[$catKey] as $ach): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="achievement-card <?php echo $ach['is_completed'] ? 'completed' : 'locked'; ?> border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="achievement-icon mb-2" style="color: <?php echo $ach['is_completed'] ? $rarityColors[$ach['rarity']] : '#6C757D'; ?>;">
                                        <i class="bi <?php echo $ach['icon']; ?> display-5"></i>
                                    </div>
                                    <div class="d-flex justify-content-center gap-1 mb-1">
                                        <span class="badge <?php echo $rarityBg[$ach['rarity']] ?? 'bg-secondary'; ?>">
                                            <?php echo $rarityLabels[$ach['rarity']] ?? 'Звичайний'; ?>
                                        </span>
                                        <?php if ($ach['is_completed']): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Отримано
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($ach['name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($ach['description']); ?></small>
                                    <div class="mt-2">
                                        <?php if ($ach['is_completed']): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-coin"></i> <?php echo $ach['points']; ?> балів
                                            </span>
                                            <?php if ($ach['unlocked_at']): ?>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <?php echo date('d.m.Y H:i', strtotime($ach['unlocked_at'])); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="progress" style="height: 4px;">
                                                <?php 
                                                $progress = min(($ach['progress'] / $ach['requirement_value']) * 100, 100);
                                                ?>
                                                <div class="progress-bar bg-primary" style="width: <?php echo $progress; ?>%;">
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo min($ach['progress'], $ach['requirement_value']); ?>/<?php echo $ach['requirement_value']; ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
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
    border-left: 4px solid transparent !important;
}

.achievement-card.completed {
    border-left-color: #FFD700 !important;
}

.achievement-card.locked {
    opacity: 0.7;
}

.achievement-card.locked .achievement-icon {
    filter: grayscale(1);
}

.achievement-card .badge {
    font-size: 0.65rem;
}

.achievement-card .progress {
    background-color: #e9ecef;
}

.achievement-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important;
}

.achievement-card.completed .achievement-icon i {
    animation: achievementGlow 2s ease-in-out infinite;
}

@keyframes achievementGlow {
    0%, 100% { filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.3)); }
    50% { filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.6)); }
}

.bg-purple {
    background-color: #9B59B6;
    color: #fff;
}

.text-purple {
    color: #9B59B6;
}
</style>