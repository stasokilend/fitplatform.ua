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
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-calendar-check"></i> Тренування</h1>
    <a href="#" class="btn btn-primary btn-sm" onclick="alert('Функція створення тренування буде додана в наступних версіях')">
        <i class="bi bi-plus-circle"></i> Нове тренування
    </a>
</div>

<div class="row g-4">
    <?php if (count($plans) > 0): ?>
        <?php foreach ($plans as $plan): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-dumbbell text-primary"></i>
                            <?php echo htmlspecialchars($plan['name']); ?>
                        </h5>
                        <div class="mb-2">
                            <span class="badge bg-<?php echo $plan['status'] === 'completed' ? 'success' : ($plan['status'] === 'in_progress' ? 'warning' : 'secondary'); ?>">
                                <?php echo $plan['status'] === 'completed' ? '✅ Завершено' : ($plan['status'] === 'in_progress' ? '⏳ В процесі' : '📋 Заплановано'); ?>
                            </span>
                        </div>
                        <p class="card-text small text-muted">
                            <i class="bi bi-clock"></i> <?php echo $plan['total_duration_min'] ?? 0; ?> хв
                            <br>
                            <i class="bi bi-calendar"></i> <?php echo date('d.m.Y', strtotime($plan['created_at'])); ?>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="#" class="btn btn-sm btn-outline-primary w-100" onclick="alert('Деталі тренування')">
                            <i class="bi bi-eye"></i> Переглянути
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-calendar-empty display-1 text-muted"></i>
                <h4 class="mt-3">Ще немає тренувань</h4>
                <p class="text-muted">Створіть своє перше тренування, щоб почати шлях до цілі!</p>
                <a href="#" class="btn btn-primary" onclick="alert('Функція створення тренування буде додана в наступних версіях')">
                    <i class="bi bi-plus-circle"></i> Створити тренування
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>