<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#6C63FF">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <title>FitPlatform - <?php echo $pageTitle ?? 'Головна'; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    
    <!-- Mobile Header (видно только на телефоне) -->
    <header class="mobile-header d-md-none">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="d-flex align-items-center gap-2">
                <button class="btn-icon" id="drawerToggle">
                    <i class="bi bi-list"></i>
                </button>
                <a href="/" class="brand">
                    <i class="bi bi-heart-pulse-fill"></i> FitPlatform
                </a>
            </div>
            <div class="header-actions">
                <a href="/dashboard.php?page=notifications" class="btn-icon position-relative">
                    <i class="bi bi-bell"></i>
                    <span class="badge-dot"></span>
                </a>
                <a href="/dashboard.php?page=profile" class="btn-icon">
                    <i class="bi bi-person"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Десктопная навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark d-none d-md-flex">
        <!-- ... существующий код ... -->
    </nav>

    <!-- Mobile Drawer (меню) -->
    <div class="mobile-drawer-overlay" id="drawerOverlay"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <div class="drawer-header">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Користувач'); ?></div>
                <div class="role">
                    <?php echo $_SESSION['user_role'] === 'trainer' ? '🏋️ Тренер' : '💪 Користувач'; ?>
                </div>
            <?php else: ?>
                <div class="avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="name">Гість</div>
            <?php endif; ?>
        </div>
        <div class="drawer-body">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' && !isset($_GET['page']) ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> Головна
                </a>
                <a href="/dashboard.php?page=workouts" class="nav-link <?php echo ($_GET['page'] ?? '') === 'workouts' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-check"></i> Тренування
                </a>
                <a href="/dashboard.php?page=workout-create" class="nav-link <?php echo ($_GET['page'] ?? '') === 'workout-create' ? 'active' : ''; ?>">
                    <i class="bi bi-plus-circle"></i> Створити
                </a>
                <a href="/dashboard.php?page=statistics" class="nav-link <?php echo ($_GET['page'] ?? '') === 'statistics' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i> Статистика
                </a>
                <a href="/dashboard.php?page=health" class="nav-link <?php echo ($_GET['page'] ?? '') === 'health' ? 'active' : ''; ?>">
                    <i class="bi bi-heart-pulse"></i> Здоров'я
                </a>
                <a href="/dashboard.php?page=achievements" class="nav-link <?php echo ($_GET['page'] ?? '') === 'achievements' ? 'active' : ''; ?>">
                    <i class="bi bi-trophy"></i> Досягнення
                </a>
                <a href="/dashboard.php?page=google-fit" class="nav-link <?php echo ($_GET['page'] ?? '') === 'google-fit' ? 'active' : ''; ?>">
                    <i class="bi bi-google"></i> Google Fit
                </a>
                <a href="/dashboard.php?page=profile" class="nav-link <?php echo ($_GET['page'] ?? '') === 'profile' ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i> Налаштування
                </a>
                <hr>
                <a href="/logout.php" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i> Вийти
                </a>
            <?php else: ?>
                <a href="/login.php" class="nav-link">
                    <i class="bi bi-box-arrow-in-right"></i> Вхід
                </a>
                <a href="/register.php" class="nav-link">
                    <i class="bi bi-person-plus"></i> Реєстрація
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Основной контент -->
    <main class="main-content" style="<?php echo isset($_SESSION['user_id']) ? 'padding-bottom: 80px;' : ''; ?>">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php echo $content; ?>
    </main>

    <!-- Mobile Bottom Navigation -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="mobile-bottom-nav d-md-none">
        <div class="container-fluid px-0">
            <div class="row g-0 text-center">
                <div class="col">
                    <a href="/dashboard.php" class="mobile-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' && !isset($_GET['page']) ? 'active' : ''; ?>">
                        <i class="bi bi-house"></i>
                        <span>Головна</span>
                    </a>
                </div>
                <div class="col">
                    <a href="/dashboard.php?page=workouts" class="mobile-nav-link <?php echo ($_GET['page'] ?? '') === 'workouts' ? 'active' : ''; ?>">
                        <i class="bi bi-calendar-check"></i>
                        <span>Тренування</span>
                    </a>
                </div>
                <div class="col">
                    <a href="/dashboard.php?page=workout-create" class="mobile-nav-link <?php echo ($_GET['page'] ?? '') === 'workout-create' ? 'active' : ''; ?>">
                        <i class="bi bi-plus-circle"></i>
                        <span>Створити</span>
                    </a>
                </div>
                <div class="col">
                    <a href="/dashboard.php?page=health" class="mobile-nav-link <?php echo ($_GET['page'] ?? '') === 'health' ? 'active' : ''; ?>">
                        <i class="bi bi-heart-pulse"></i>
                        <span>Здоров'я</span>
                    </a>
                </div>
                <div class="col">
                    <a href="/dashboard.php?page=profile" class="mobile-nav-link <?php echo ($_GET['page'] ?? '') === 'profile' ? 'active' : ''; ?>">
                        <i class="bi bi-person"></i>
                        <span>Профіль</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Footer (скрыт на мобильных) -->
    <footer class="bg-dark text-white py-4 mt-auto d-none d-md-block">
        <!-- ... существующий код ... -->
    </footer>

    <!-- Контейнер для уведомлений -->
    <div id="notificationContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Custom JS -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/mobile.js"></script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/dashboard.js"></script>
    <script src="/assets/js/notifications.js"></script>
    <script src="/assets/js/profile.js"></script>
    <script src="/assets/js/workout.js"></script>
    <script src="/assets/js/charts.js"></script>
    <script src="/assets/js/gamification.js"></script>
</body>
</html>