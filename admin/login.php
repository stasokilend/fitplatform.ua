<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/admin-auth.php';

// Если уже залогинен - в админку
if (isAdminLoggedIn()) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (adminLogin($email, $password)) {
        header('Location: /admin/index.php');
        exit;
    } else {
        $error = 'Невірний email або пароль, або недостатньо прав';
    }
}

$pageTitle = 'Адмін-вхід';
ob_start();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitPlatform - Адмін-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-login-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .admin-login-card .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .admin-login-card .logo i {
            font-size: 48px;
            color: #0d6efd;
        }
        .admin-login-card .logo h2 {
            margin-top: 10px;
            font-weight: 700;
        }
        .admin-login-card .logo small {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="admin-login-card">
        <div class="logo">
            <i class="bi bi-shield-lock-fill"></i>
            <h2>Адмін-панель</h2>
            <small>FitPlatform</small>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Пароль</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right"></i> Увійти
            </button>
        </form>
        
        <div class="text-center mt-3">
            <a href="/" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left"></i> Повернутися на сайт
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>