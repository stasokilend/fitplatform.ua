<?php
require_once 'includes/session.php';
requireLogin();

require_once 'config/database.php';
require_once 'controllers/ProfileController.php';
require_once 'includes/functions.php';

$userId = $_SESSION['user_id'];
$profile = getUserProfile($userId);
$stats = getDashboardStats($userId);
$role = $_SESSION['user_role'];

$pageTitle = 'Кабінет';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Боковое меню -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar py-4 vh-100 border-end">
            <div class="position-sticky">
                <div class="text-center mb-4">
                    <div class="display-1 text-primary">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                    <span class="badge bg-secondary">
                        <?php echo $role === 'trainer' ? '🏋️ Тренер' : '💪 Користувач'; ?>
                    </span>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActivePage('dashboard.php') && !isset($_GET['page']) ? 'active' : ''; ?>" 
                           href="/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Головна
                        </a>
                    </li>
                    
                    <?php if ($role === 'user' || $role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard.php?page=workouts">
                                <i class="bi bi-calendar-check"></i> Тренування
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard.php?page=statistics">
                                <i class="bi bi-graph-up"></i> Статистика
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard.php?page=profile">
                                <i class="bi bi-gear"></i> Профіль
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'trainer' || $role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard.php?page=clients">
                                <i class="bi bi-people"></i> Клієнти
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard.php?page=programs">
                                <i class="bi bi-file-text"></i> Програми
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Вийти
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Контент -->
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <?php
            $page = $_GET['page'] ?? 'index';
            
            if ($role === 'trainer') {
                $baseDir = 'views/dashboard/trainer/';
            } else {
                $baseDir = 'views/dashboard/user/';
            }
            
            $file = $baseDir . $page . '.php';
            
            if ($page === 'index' || !file_exists($file)) {
                // Главная дашборда
                ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-speedometer2"></i> Головна</h1>
                    <div class="btn-toolbar">
                        <span class="text-muted">
                            <i class="bi bi-clock"></i> <?php echo date('d.m.Y H:i'); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Статистика -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title text-white-50"><i class="bi bi-calendar-check"></i> Тренувань</h6>
                                <h2 class="mb-0"><?php echo $stats['total_workouts']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title text-white-50"><i class="bi bi-check-circle"></i> Завершено</h6>
                                <h2 class="mb-0"><?php echo $stats['completed_workouts']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6 class="card-title text-dark-50"><i class="bi bi-fire"></i> Калорій</h6>
                                <h2 class="mb-0"><?php echo number_format($stats['total_calories'], 0); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title text-white-50"><i class="bi bi-person-badge"></i> Рівень</h6>
                                <h2 class="mb-0"><?php echo getFitnessLevelLabel($profile['fitness_level'] ?? 'beginner'); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Профиль кратко -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-person"></i> Мій профіль
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Вік</small>
                                        <p><?php echo $profile['age'] ?? '-'; ?> років</p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Вага</small>
                                        <p><?php echo $profile['weight'] ?? '-'; ?> кг</p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Зріст</small>
                                        <p><?php echo $profile['height'] ?? '-'; ?> см</p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Мета</small>
                                        <p><?php echo getGoalTypeLabel($profile['goal_type'] ?? 'health'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-clock-history"></i> Остання активність
                            </div>
                            <div class="card-body">
                                <?php if ($stats['last_workout']): ?>
                                    <p><strong><?php echo htmlspecialchars($stats['last_workout']['name']); ?></strong></p>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> <?php echo date('d.m.Y', strtotime($stats['last_workout']['created_at'])); ?>
                                        <span class="mx-2">•</span>
                                        <i class="bi bi-clock"></i> <?php echo $stats['last_workout']['total_duration_min']; ?> хв
                                    </small>
                                    <div class="mt-2">
                                        <span class="badge bg-<?php echo $stats['last_workout']['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo $stats['last_workout']['status'] === 'completed' ? '✓ Завершено' : '⏳ В процесі'; ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Немає активності</p>
                                    <a href="/dashboard.php?page=workouts" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle"></i> Почати тренування
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($role === 'trainer'): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-people"></i> Мої клієнти
                            </div>
                            <div class="card-body text-center text-muted">
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
                
                <?php
            } else {
                require_once $file;
            }
            ?>
        </main>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once 'views/layout.php';
?>