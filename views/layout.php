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
    
    <!-- Mobile Header (только для авторизованных пользователей) -->
    <?php if (isset($_SESSION['user_id'])): ?>
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
                <a href="/dashboard.php?page=health" class="btn-icon position-relative">
                    <i class="bi bi-heart-pulse"></i>
                </a>
                <a href="/dashboard.php?page=settings" class="btn-icon">
                    <i class="bi bi-gear"></i>
                </a>
            </div>
        </div>
    </header>
    <?php endif; ?>

    <!-- Десктопная навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark d-none d-md-flex">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-heart-pulse-fill"></i> FitPlatform
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Вхід
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link btn btn-primary text-white px-4 rounded-pill" href="/register.php">
                                <i class="bi bi-person-plus"></i> Реєстрація
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- В десктопной навигации, после кнопки Кабинет -->
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="#" id="notificationBell" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell" style="font-size: 1.2rem;"></i>
                            <span class="notification-badge" id="desktopBadge" style="display: none; position: absolute; top: 0; right: 0; background: #dc3545; color: white; border-radius: 50%; font-size: 0.6rem; padding: 2px 6px; min-width: 18px; text-align: center;">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0" aria-labelledby="notificationBell" style="width: 380px; max-height: 500px; overflow: hidden;">
                            <div class="notification-header d-flex justify-content-between align-items-center p-3 border-bottom">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-bell text-primary"></i> Сповіщення</h6>
                                <div>
                                    <button class="btn btn-sm btn-link text-decoration-none" id="markAllReadBtn">Позначити всі</button>
                                    <button class="btn btn-sm btn-link text-danger text-decoration-none" id="clearAllBtn">Очистити</button>
                                </div>
                            </div>
                            <div class="notification-list" id="notificationList" style="max-height: 380px; overflow-y: auto;">
                                <div class="text-center py-4 text-muted" id="notificationEmpty">
                                    <i class="bi bi-bell-slash display-6 d-block mb-2"></i>
                                    <span>Немає сповіщень</span>
                                </div>
                            </div>
                            <div class="notification-footer p-2 border-top text-center">
                                <button class="btn btn-sm btn-outline-primary w-100" id="loadMoreBtn">Завантажити ще</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Drawer (меню) -->
    <?php
    $layoutActualRole = $_SESSION['user_role'] ?? null;
    $layoutRole = $layoutActualRole === 'admin' ? ($_SESSION['admin_test_role'] ?? 'user') : $layoutActualRole;
    ?>
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="mobile-drawer-overlay" id="drawerOverlay"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <div class="drawer-header">
            <div class="avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Користувач'); ?></div>
            <div class="role">
                <?php 
                if ($layoutActualRole === 'admin') {
                    echo '👑 Адміністратор';
                    echo $layoutRole === 'trainer' ? ' · тест: тренер' : ' · тест: користувач';
                } elseif ($layoutRole === 'trainer') {
                    echo '🏋️ Тренер';
                } else {
                    echo '💪 Користувач';
                }
                ?>
            </div>
        </div>
        <div class="drawer-body">
            <!-- Кабинет -->
            <a href="/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' && !isset($_GET['page']) ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Кабінет
            </a>
            
            <?php if ($layoutRole !== 'trainer'): ?>
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
                <a href="/dashboard.php?page=chat" class="nav-link <?php echo ($_GET['page'] ?? '') === 'chat' ? 'active' : ''; ?>">
                    <i class="bi bi-chat-dots"></i> Чат
                </a>
                <a href="/dashboard.php?page=achievements" class="nav-link <?php echo ($_GET['page'] ?? '') === 'achievements' ? 'active' : ''; ?>">
                    <i class="bi bi-trophy"></i> Досягнення
                </a>
            <?php else: ?>
                <a href="/dashboard.php?page=clients" class="nav-link <?php echo ($_GET['page'] ?? '') === 'clients' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i> Клієнти
                </a>
                <a href="/dashboard.php?page=programs" class="nav-link <?php echo ($_GET['page'] ?? '') === 'programs' ? 'active' : ''; ?>">
                    <i class="bi bi-file-text"></i> Програми
                </a>
                <a href="/dashboard.php?page=chat" class="nav-link <?php echo ($_GET['page'] ?? '') === 'chat' ? 'active' : ''; ?>">
                    <i class="bi bi-chat-dots"></i> Чат
                </a>
                <a href="/dashboard.php?page=schedule" class="nav-link <?php echo ($_GET['page'] ?? '') === 'schedule' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar"></i> Розклад
                </a>
            <?php endif; ?>
            
            <!-- Настройки (для всех) -->
            <a href="/dashboard.php?page=settings" class="nav-link <?php echo ($_GET['page'] ?? '') === 'settings' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> Налаштування
            </a>
            
            <?php if ($layoutActualRole === 'admin'): ?>
                <a href="/admin/index.php" class="nav-link">
                    <i class="bi bi-shield-lock"></i> Адмін-панель
                </a>
            <?php endif; ?>
            
            <hr>
            <a href="/logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right"></i> Вийти
            </a>
        </div>
    </div>
    <?php endif; ?>

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
                
                <?php if ($layoutRole !== 'trainer'): ?>
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
                <?php else: ?>
                    <div class="col">
                        <a href="/dashboard.php?page=clients" class="mobile-nav-link <?php echo ($_GET['page'] ?? '') === 'clients' ? 'active' : ''; ?>">
                            <i class="bi bi-people"></i>
                            <span>Клієнти</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/dashboard.php?page=programs" class="mobile-nav-link <?php echo ($_GET['page'] ?? '') === 'programs' ? 'active' : ''; ?>">
                            <i class="bi bi-file-text"></i>
                            <span>Програми</span>
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="col">
                    <a href="/dashboard.php?page=health" class="mobile-nav-link <?php echo ($_GET['page'] ?? '') === 'health' ? 'active' : ''; ?>">
                        <i class="bi bi-heart-pulse"></i>
                        <span>Здоров'я</span>
                    </a>
                </div>
                <div class="col">
                    <a href="/dashboard.php?page=settings" class="mobile-nav-link <?php echo ($_GET['page'] ?? '') === 'settings' ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i>
                        <span>Налаштування</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start">
                    <span class="fw-bold fs-5">
                        <i class="bi bi-heart-pulse-fill text-primary"></i> FitPlatform
                    </span>
                    <small class="text-white-50 d-block mt-1">© <?php echo date('Y'); ?> Всі права захищено</small>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="text-white-50 text-decoration-none hover-effect">
                            <i class="bi bi-twitter-x fs-5"></i>
                        </a>
                        <a href="#" class="text-white-50 text-decoration-none hover-effect">
                            <i class="bi bi-instagram fs-5"></i>
                        </a>
                        <a href="#" class="text-white-50 text-decoration-none hover-effect">
                            <i class="bi bi-youtube fs-5"></i>
                        </a>
                        <a href="#" class="text-white-50 text-decoration-none hover-effect">
                            <i class="bi bi-telegram fs-5"></i>
                        </a>
                        <a href="#" class="text-white-50 text-decoration-none hover-effect">
                            <i class="bi bi-github fs-5"></i>
                        </a>
                        <a href="#" class="text-white-50 text-decoration-none hover-effect">
                            <i class="bi bi-tiktok fs-5"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4 text-center text-md-end">
                    <div class="d-flex flex-column align-items-center align-items-md-end">
                        <span class="text-white-50 small">
                            <i class="bi bi-code-slash text-primary"></i> Розроблено з ❤️
                        </span>
                        <span class="text-white fw-semibold">
                            <i class="bi bi-person-heart text-primary"></i> stasokilend
                        </span>
                    </div>
                </div>
            </div>
            
            <hr class="border-secondary opacity-25 my-3">
            
            <div class="row text-center text-white-50 small">
                <div class="col-12">
                    <span>FitPlatform — твій шлях до здорового тіла та гарного настрою 💪</span>
                </div>
            </div>
        </div>
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
<script>
// Проверка непрочитанных сообщений
function checkUnreadMessages() {
    fetch('/api/chat.php?action=unread')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                const badge = document.getElementById('chatBadge');
                if (badge) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'inline';
                }
            }
        });
}

// Проверяем каждые 30 секунд
setInterval(checkUnreadMessages, 30000);
checkUnreadMessages();
</script>
</html>