<?php require_once __DIR__ . '/../../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitPlatform - Адмін-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset('/admin/assets/css/admin.css'); ?>" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark text-white" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom">
                <i class="bi bi-shield-fill-check text-primary"></i> FitPlatform
                <small class="d-block text-muted fs-6">Адмін-панель</small>
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="/admin/index.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Головна
                </a>
                <a href="/admin/users.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people me-2"></i> Користувачі
                </a>
                <a href="/admin/exercises.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo basename($_SERVER['PHP_SELF']) === 'exercises.php' ? 'active' : ''; ?>">
                    <i class="bi bi-dumbbell me-2"></i> Вправи
                </a>
                <a href="/admin/restrictions.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo basename($_SERVER['PHP_SELF']) === 'restrictions.php' ? 'active' : ''; ?>">
                    <i class="bi bi-heart-pulse me-2"></i> Обмеження
                </a>
                <a href="/admin/logout.php" class="list-group-item list-group-item-action bg-transparent text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i> Вихід
                </a>
            </div>
        </div>
        
        <!-- Page content -->
        <div id="page-content-wrapper">
            <?php echo $content; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('/admin/assets/js/admin.js'); ?>"></script>
</body>
</html>