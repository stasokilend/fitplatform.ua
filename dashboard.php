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
        <nav class="col-md-3 col-lg-2 d-md-block sidebar py-4 vh-100 border-end">
            <div class="position-sticky">
                <div class="text-center mb-4">
                    <div class="user-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5 class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                    <span class="user-role badge bg-<?php echo $role === 'trainer' ? 'warning' : 'primary'; ?>">
                        <?php echo $role === 'trainer' ? '🏋️ Тренер' : '💪 Користувач'; ?>
                    </span>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] === 'index') ? 'active' : ''; ?>" 
                           href="/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Головна
                        </a>
                    </li>
                    
                    <?php if ($role === 'user' || $role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($_GET['page'] ?? '') === 'workouts' ? 'active' : ''; ?>" 
                               href="/dashboard.php?page=workouts">
                                <i class="bi bi-calendar-check"></i> Тренування
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($_GET['page'] ?? '') === 'statistics' ? 'active' : ''; ?>" 
                               href="/dashboard.php?page=statistics">
                                <i class="bi bi-graph-up"></i> Статистика
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($role === 'trainer' || $role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($_GET['page'] ?? '') === 'clients' ? 'active' : ''; ?>" 
                               href="/dashboard.php?page=clients">
                                <i class="bi bi-people"></i> Клієнти
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($_GET['page'] ?? '') === 'programs' ? 'active' : ''; ?>" 
                               href="/dashboard.php?page=programs">
                                <i class="bi bi-file-text"></i> Програми
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($_GET['page'] ?? '') === 'profile' ? 'active' : ''; ?>" 
                           href="/dashboard.php?page=profile">
                            <i class="bi bi-gear"></i> Налаштування
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($_GET['page'] ?? '') === 'workout-create' ? 'active' : ''; ?>" 
                        href="/dashboard.php?page=workout-create">
                            <i class="bi bi-plus-circle"></i> Створити
                        </a>
                    </li>
                    
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
            
            // Определяем директорию в зависимости от роли
            if ($role === 'trainer') {
                $baseDir = 'views/dashboard/trainer/';
            } else {
                $baseDir = 'views/dashboard/user/';
            }
            
            // Проверяем существование файла
            $file = $baseDir . $page . '.php';
            
            if ($page === 'index' || !file_exists($file)) {
                // Главная дашборда
                require_once 'views/dashboard/index.php';
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