<?php
require_once 'controllers/TrainerController.php';

$trainer = new TrainerController($userId);
$stats = $trainer->getStats();

// Получаем последних активных клиентов
$clients = $trainer->getClients('active');
$recentClients = array_slice($clients, 0, 5);
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-person-badge text-primary"></i> Панель тренера
        </h1>
        <span class="text-muted">
            <i class="bi bi-calendar"></i> <?php echo date('d.m.Y H:i'); ?>
        </span>
    </div>

    <!-- Статистика -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-primary mx-auto mb-2">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-number"><?php echo $stats['active_clients']; ?></div>
                <div class="stat-label">Активні клієнти</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-success mx-auto mb-2">
                    <i class="bi bi-file-text"></i>
                </div>
                <div class="stat-number"><?php echo $stats['programs']; ?></div>
                <div class="stat-label">Програми</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-warning mx-auto mb-2">
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="stat-number"><?php echo $stats['unread_messages']; ?></div>
                <div class="stat-label">Непрочитані</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-icon bg-danger mx-auto mb-2">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-number"><?php echo $stats['today_sessions']; ?></div>
                <div class="stat-label">Сьогодні</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Последние клиенты -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people text-primary"></i> Останні клієнти
                    </h5>
                    <a href="/dashboard.php?page=clients" class="btn btn-sm btn-outline-primary">
                        Всі <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recentClients) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentClients as $client): ?>
                                <a href="/dashboard.php?page=client-detail&id=<?php echo $client['id']; ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">
                                            <?php echo htmlspecialchars($client['full_name']); ?>
                                            <?php if ($client['unread_messages'] > 0): ?>
                                                <span class="badge bg-danger rounded-pill ms-1"><?php echo $client['unread_messages']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo $client['goal_type'] ? getGoalTypeLabel($client['goal_type']) : 'Мета не вказана'; ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $client['fitness_level'] === 'advanced' ? 'danger' : ($client['fitness_level'] === 'intermediate' ? 'warning' : 'success'); ?>">
                                        <?php echo getFitnessLevelLabel($client['fitness_level']); ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-people display-4 d-block mb-2"></i>
                            <p>Ще немає клієнтів</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning text-primary"></i> Швидкі дії
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/dashboard.php?page=program-create" class="btn btn-primary btn-gradient">
                            <i class="bi bi-plus-circle"></i> Створити програму
                        </a>
                        <a href="/dashboard.php?page=clients" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus"></i> Додати клієнта
                        </a>
                        <a href="/dashboard.php?page=messages" class="btn btn-outline-success">
                            <i class="bi bi-chat"></i> Чат
                            <?php if ($stats['unread_messages'] > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo $stats['unread_messages']; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/dashboard.php?page=schedule" class="btn btn-outline-warning">
                            <i class="bi bi-calendar"></i> Розклад
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>