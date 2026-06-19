<?php
require_once 'config/database.php';
$userId = $_SESSION['user_id'];

// Получаем планы тренировок
$stmt = $pdo->prepare("
    SELECT * FROM workout_plans 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$plans = $stmt->fetchAll();

// Статистика тренировок
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
    FROM workout_plans
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$workoutStats = $stmt->fetch();
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-calendar-check text-primary"></i> Тренування
        </h1>
        <div>
            <span class="text-muted me-3">
                <i class="bi bi-check-circle text-success"></i> 
                <?php echo $workoutStats['completed'] ?? 0; ?>/<?php echo $workoutStats['total'] ?? 0; ?>
            </span>
            <a href="/dashboard.php?page=workout-create" class="btn btn-primary btn-gradient">
                <i class="bi bi-plus-circle"></i> Нове тренування
            </a>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <button class="btn btn-outline-primary btn-sm active filter-btn" data-status="all">
            <i class="bi bi-list"></i> Всі
        </button>
        <button class="btn btn-outline-success btn-sm filter-btn" data-status="completed">
            <i class="bi bi-check-circle"></i> Завершені
        </button>
        <button class="btn btn-outline-warning btn-sm filter-btn" data-status="in_progress">
            <i class="bi bi-clock"></i> В процесі
        </button>
        <button class="btn btn-outline-secondary btn-sm filter-btn" data-status="planned">
            <i class="bi bi-calendar"></i> Заплановані
        </button>
    </div>

    <div class="row g-4" id="workoutsContainer">
        <?php if (count($plans) > 0): ?>
            <?php foreach ($plans as $plan): 
                // Прогресс
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN is_completed = 1 THEN 1 END) as completed
                    FROM plan_exercises
                    WHERE plan_id = ?
                ");
                $stmt->execute([$plan['id']]);
                $progress = $stmt->fetch();
                $progressPercent = ($progress['total'] > 0) ? round(($progress['completed'] / $progress['total']) * 100) : 0;
            ?>
                <div class="col-md-6 col-lg-4 workout-card-item">
                    <div class="workout-card" data-status="<?php echo $plan['status']; ?>">
                        <div class="card-header-custom">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">
                                    <i class="bi bi-dumbbell me-2"></i>
                                    <?php echo htmlspecialchars($plan['name']); ?>
                                </span>
                                <span class="badge bg-white bg-opacity-25">
                                    <?php echo $plan['status'] === 'completed' ? '✅' : ($plan['status'] === 'in_progress' ? '⏳' : '📋'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge bg-<?php echo $plan['status'] === 'completed' ? 'success' : ($plan['status'] === 'in_progress' ? 'warning' : 'secondary'); ?>">
                                    <?php echo $plan['status'] === 'completed' ? 'Завершено' : ($plan['status'] === 'in_progress' ? 'В процесі' : 'Заплановано'); ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span><i class="bi bi-clock"></i> <?php echo $plan['total_duration_min'] ?? 0; ?> хв</span>
                                <span><i class="bi bi-calendar"></i> <?php echo date('d.m.Y', strtotime($plan['created_at'])); ?></span>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small">
                                    <span>Прогрес</span>
                                    <span><?php echo $progressPercent; ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php echo $progressPercent >= 100 ? 'success' : 'primary'; ?>" 
                                         style="width: <?php echo $progressPercent; ?>%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <?php if ($plan['status'] !== 'completed'): ?>
                                <a href="/dashboard.php?page=workout-track&id=<?php echo $plan['id']; ?>" 
                                   class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-play"></i> Продовжити
                                </a>
                            <?php else: ?>
                                <a href="/dashboard.php?page=workout-detail&id=<?php echo $plan['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-eye"></i> Деталі
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-empty display-1 d-block mb-3"></i>
                    <h4 class="fw-semibold">Ще немає тренувань</h4>
                    <p>Створіть своє перше тренування, щоб почати шлях до цілі!</p>
                    <a href="/dashboard.php?page=workout-create" class="btn btn-primary btn-gradient">
                        <i class="bi bi-plus-circle"></i> Створити тренування
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Фильтрация тренировок
    document.querySelectorAll('.filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            const status = this.dataset.status;
            const cards = document.querySelectorAll('.workout-card-item');
            
            cards.forEach(function(card) {
                const cardStatus = card.querySelector('.workout-card').dataset.status;
                if (status === 'all' || cardStatus === status) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>