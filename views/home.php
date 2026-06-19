<?php 
$pageTitle = 'Головна';
ob_start();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6 text-white fade-in-up">
                <span class="badge bg-primary bg-opacity-25 text-primary-light mb-3 px-4 py-2 rounded-pill" style="color: #8B83FF !important;">
                    <i class="bi bi-rocket-fill me-1"></i> Нова платформа
                </span>
                <h1 class="hero-title">
                    FitPlatform
                </h1>
                <p class="hero-subtitle">
                    Персоналізовані фітнес-тренування та моніторинг здоров'я 
                    з використанням штучного інтелекту
                </p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="hero-buttons d-flex gap-3 flex-wrap">
                        <a href="/dashboard.php" class="btn btn-primary btn-gradient">
                            <i class="bi bi-speedometer2"></i> Перейти до кабінету
                        </a>
                        <a href="/profile-setup.php" class="btn btn-outline-light">
                            <i class="bi bi-gear"></i> Налаштувати профіль
                        </a>
                    </div>
                    <div class="mt-3">
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i> 
                            Ви вже в системі, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Користувач'); ?> 👋
                        </small>
                    </div>
                <?php else: ?>
                    <div class="hero-buttons d-flex gap-3 flex-wrap">
                        <a href="/register.php" class="btn btn-primary btn-gradient">
                            <i class="bi bi-person-plus"></i> Почати тренуватися
                        </a>
                        <a href="/login.php" class="btn btn-outline-light">
                            <i class="bi bi-box-arrow-in-right"></i> Увійти
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-6 text-center d-none d-lg-block hero-image">
                <div class="position-relative">
                    <div class="bg-primary bg-opacity-10 rounded-circle" style="width: 400px; height: 400px; margin: 0 auto; position: relative;">
                        <i class="bi bi-heart-pulse-fill" style="font-size: 180px; color: #6C63FF; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>
                    </div>
                    <!-- Декоративные элементы -->
                    <div class="position-absolute top-0 start-0">
                        <i class="bi bi-dumbbell" style="font-size: 40px; color: #FF6584; opacity: 0.6;"></i>
                    </div>
                    <div class="position-absolute bottom-0 end-0">
                        <i class="bi bi-graph-up-arrow" style="font-size: 50px; color: #00D2A0; opacity: 0.6;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Преимущества -->
<section class="features-section fade-in-up">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-pill">
                Переваги
            </span>
            <h2 class="display-6 fw-bold mt-3">Чому обирають FitPlatform</h2>
            <p class="text-muted">Сучасний підхід до фітнесу та здоров'я</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <h5>Персоналізація</h5>
                    <p>Тренування адаптовані під ваші цілі, стан здоров'я та рівень підготовки</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #00D2A0 0%, #00A87E 100%);">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h5>Відстеження прогресу</h5>
                    <p>Аналітика та графіки вашого прогресу в реальному часі</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #FF6584 0%, #FF4470 100%);">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <h5>Безпека</h5>
                    <p>Моніторинг пульсу та контроль навантаження для запобігання травм</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #FFB347 0%, #FF8C00 100%);">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5>Гнучкий графік</h5>
                    <p>Тренуйтеся вдома або в залі, у зручний для вас час</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #6C63FF 0%, #5A52D5 100%);">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5>Підтримка тренера</h5>
                    <p>Професійні тренери допоможуть досягти ваших цілей</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #6C757D 0%, #495057 100%);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Безпека даних</h5>
                    <p>Ваші медичні та персональні дані надійно захищені</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA секция -->
<section class="py-5 bg-gradient-primary text-white fade-in-up">
    <div class="container text-center">
        <h2 class="display-6 fw-bold">Готові почати свій шлях до здорового тіла?</h2>
        <p class="opacity-75 mb-4">Приєднуйтесь до тисяч користувачів, які вже змінюють своє життя</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="/register.php" class="btn btn-light btn-lg rounded-pill px-5 shadow-lg">
                <i class="bi bi-rocket-fill me-2"></i> Розпочати безкоштовно
            </a>
        <?php else: ?>
            <a href="/dashboard.php" class="btn btn-light btn-lg rounded-pill px-5 shadow-lg">
                <i class="bi bi-arrow-right"></i> Перейти в кабінет
            </a>
        <?php endif; ?>
    </div>
</section>

<?php 
$content = ob_get_clean();
require_once 'layout.php';
?>