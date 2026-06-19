<?php
// Эта страница вставляется в dashboard.php
// Все данные уже получены в dashboard.php
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-speedometer2 text-primary"></i> Головна
        </h1>
        <div>
            <span class="text-muted">
                <i class="bi bi-clock"></i> <?php echo date('d.m.Y H:i'); ?>
            </span>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Тренувань</div>
                        <div class="stat-number"><?php echo $stats['total_workouts']; ?></div>
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
                        <div class="stat-number text-success"><?php echo $stats['completed_workouts']; ?></div>
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
                        <div class="stat-label">Калорій спалено</div>
                        <div class="stat-number text-warning"><?php echo number_format($stats['total_calories'], 0); ?></div>
                    </div>
                    <div class="stat-icon bg-warning">
                        <i class="bi bi-fire"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Рівень</div>
                        <div class="stat-number text-info" style="font-size: 1.2rem;">
                            <?php echo getFitnessLevelLabel($profile['fitness_level'] ?? 'beginner'); ?>
                        </div>
                    </div>
                    <div class="stat-icon bg-info">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Профиль кратко -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bi bi-person text-primary"></i> Мій профіль</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted d-block">Вік</small>
                            <p class="fw-semibold"><?php echo $profile['age'] ?? '-'; ?> років</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Вага</small>
                            <p class="fw-semibold"><?php echo $profile['weight'] ?? '-'; ?> кг</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Зріст</small>
                            <p class="fw-semibold"><?php echo $profile['height'] ?? '-'; ?> см</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Мета</small>
                            <p class="fw-semibold"><?php echo getGoalTypeLabel($profile['goal_type'] ?? 'health'); ?></p>
                        </div>
                        <?php if ($profile['target_weight']): ?>
                        <div class="col-12">
                            <small class="text-muted d-block">Цільова вага</small>
                            <p class="fw-semibold"><?php echo $profile['target_weight']; ?> кг</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($profile['medical_notes']): ?>
                        <div class="mt-2">
                            <small class="text-muted d-block">Медичні обмеження</small>
                            <p class="small text-muted"><?php echo htmlspecialchars($profile['medical_notes']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Последняя активность -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-primary"></i> Остання активність</h5>
                </div>
                <div class="card-body">
                    <?php if ($stats['last_workout']): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="bi bi-dumbbell display-6 text-primary"></i>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0"><?php echo htmlspecialchars($stats['last_workout']['name']); ?></p>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> <?php echo date('d.m.Y', strtotime($stats['last_workout']['created_at'])); ?>
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-clock"></i> <?php echo $stats['last_workout']['total_duration_min']; ?> хв
                                </small>
                            </div>
                            <span class="badge bg-<?php echo $stats['last_workout']['status'] === 'completed' ? 'success' : 'warning'; ?> ms-auto">
                                <?php echo $stats['last_workout']['status'] === 'completed' ? '✅ Завершено' : '⏳ В процесі'; ?>
                            </span>
                        </div>
                        <a href="/dashboard.php?page=workouts" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-list"></i> Всі тренування
                        </a>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                            <p>Немає активності</p>
                            <a href="/dashboard.php?page=workouts" class="btn btn-sm btn-primary btn-gradient">
                                <i class="bi bi-plus-circle"></i> Почати тренування
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($role === 'trainer'): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-people text-primary"></i> Мої клієнти</h5>
                    </div>
                    <div class="card-body text-center text-muted py-4">
                        <i class="bi bi-people display-4 d-block mb-2"></i>
                        <p>Тут буде список ваших клієнтів</p>
                        <a href="/dashboard.php?page=clients" class="btn btn-sm btn-outline-primary">
                            Переглянути
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Получаем данные геймификации для виджета
require_once 'config/database.php';
require_once 'controllers/GamificationController.php';

$gamification = new GamificationController($userId);
$stats = $gamification->getGamificationStats();
$recentAchievements = $gamification->getRecentAchievements(3);
?>

<!-- Виджет геймификации -->
<div class="col-md-6">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-trophy text-warning"></i> Мої досягнення
            </h5>
            <a href="/dashboard.php?page=achievements" class="btn btn-sm btn-outline-primary">
                Всі <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="row text-center mb-3">
                <div class="col-4">
                    <div class="display-6 text-warning"><?php echo $stats['level']; ?></div>
                    <small class="text-muted">Рівень</small>
                </div>
                <div class="col-4">
                    <div class="display-6 text-primary"><?php echo $stats['completed_achievements']; ?></div>
                    <small class="text-muted">Досягнень</small>
                </div>
                <div class="col-4">
                    <div class="display-6 text-success"><?php echo $stats['streak']; ?></div>
                    <small class="text-muted">🔥 Серія</small>
                </div>
            </div>
            
            <?php if (count($recentAchievements) > 0): ?>
                <div class="border-top pt-2">
                    <small class="text-muted d-block mb-2">Останні досягнення:</small>
                    <?php foreach ($recentAchievements as $ach): ?>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-warning me-2">
                                <i class="bi <?php echo $ach['icon']; ?>"></i>
                            </span>
                            <span class="small"><?php echo htmlspecialchars($ach['name']); ?></span>
                            <span class="ms-auto small text-muted">
                                <?php echo date('d.m.Y', strtotime($ach['unlocked_at'])); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-2">
                    <small>Продовжуйте тренуватися, щоб отримати перше досягнення!</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>