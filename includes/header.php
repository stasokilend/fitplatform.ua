<?php
// includes/header.php - Універсальний хедер для всіх сторінок

// Визначаємо поточну сторінку для підсвічування активного пункту
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdminPage = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;

// Якщо ми в адмінці, коригуємо шляхи
$basePath = $isAdminPage ? '../' : '';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'FitPlatform'; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js (для сторінок з графіками) -->
    <?php if (in_array($currentPage, ['dashboard.php', 'stats.php', 'admin/stats.php', 'admin/index.php'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    <!-- Власні стилі -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body>
    <!-- Контейнер для тостів -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- Навігація -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $basePath; ?>dashboard.php">
                <i class="fas fa-heartbeat me-2"></i>FitPlatform
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Головні сторінки -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" 
                           href="<?php echo $basePath; ?>dashboard.php">
                            <i class="fas fa-home"></i> Кабінет
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'stats.php' ? 'active' : ''; ?>" 
                           href="<?php echo $basePath; ?>stats.php">
                            <i class="fas fa-chart-line"></i> Статистика
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>" 
                           href="<?php echo $basePath; ?>settings.php">
                            <i class="fas fa-cog"></i> Налаштування
                        </a>
                    </li>
                    
                    <!-- Адмін-панель (показується тільки для адмінів/тренерів) -->
                    <li class="nav-item" id="adminNavItem" style="display:none;">
                        <a class="nav-link <?php echo $isAdminPage ? 'active' : ''; ?>" 
                           href="<?php echo $basePath; ?>admin/index.php">
                            <i class="fas fa-shield-alt"></i> Адмін
                        </a>
                    </li>
                    
                    <!-- Вихід -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="logoutBtn">
                            <i class="fas fa-sign-out-alt"></i> Вихід
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Основний контент починається тут -->
    <main>