<?php
require_once 'config/database.php';

$userId = $_SESSION['user_id'];
$planId = (int)($_GET['id'] ?? 0);

if (!$planId) {
    header('Location: /dashboard.php?page=workouts');
    exit;
}

// Получаем данные тренировки
$stmt = $pdo->prepare("
    SELECT w.*, 
           COUNT(pe.id) as total_exercises,
           COUNT(CASE WHEN pe.is_completed = 1 THEN 1 END) as completed_exercises,
           (SELECT COUNT(*) FROM workout_logs WHERE plan_id = w.id) as has_log
    FROM workout_plans w
    LEFT JOIN plan_exercises pe ON w.id = pe.plan_id
    WHERE w.id = ? AND w.user_id = ?
    GROUP BY w.id
");
$stmt->execute([$planId, $userId]);
$workout = $stmt->fetch();

if (!$workout) {
    header('Location: /dashboard.php?page=workouts');
    exit;
}

// Получаем упражнения
$stmt = $pdo->prepare("
    SELECT pe.*, e.name, e.muscle_group, e.difficulty
    FROM plan_exercises pe
    JOIN exercises e ON pe.exercise_id = e.id
    WHERE pe.plan_id = ?
    ORDER BY pe.order_num
");
$stmt->execute([$planId]);
$exercises = $stmt->fetchAll();

$progress = $workout['total_exercises'] > 0 
    ? round(($workout['completed_exercises'] / $workout['total_exercises']) * 100) 
    : 0;

$isCompleted = $workout['status'] === 'completed';
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h2">
                <i class="bi bi-file-text text-primary"></i> 
                <?php echo htmlspecialchars($workout['name']); ?>
            </h1>
            <small class="text-muted">
                <i class="bi bi-calendar"></i> <?php echo date('d.m.Y H:i', strtotime($workout['created_at'])); ?>
                <span class="mx-2">•</span>
                <i class="bi bi-clock"></i> <?php echo $workout['total_duration_min']; ?> хв
            </small>
        </div>
        <div>
            <a href="/dashboard.php?page=workouts" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
            <?php if (!$isCompleted): ?>
                <a href="/dashboard.php?page=workout-track&id=<?php echo $planId; ?>" class="btn btn-primary">
                    <i class="bi bi-play"></i> Продовжити
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center">
                        <div class="display-1 text-<?php echo $isCompleted ? 'success' : 'warning'; ?>">
                            <i class="bi bi-<?php echo $isCompleted ? 'check-circle-fill' : 'hourglass-split'; ?>"></i>
                        </div>
                        <h4 class="mt-2"><?php echo $isCompleted ? '✅ Завершено' : '⏳ В процесі'; ?></h4>
                        <p class="text-muted">
                            <?php echo $workout['completed_exercises']; ?>/<?php echo $workout['total_exercises']; ?> вправ виконано
                        </p>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-<?php echo $progress >= 100 ? 'success' : 'primary'; ?>" 
                                 style="width: <?php echo $progress; ?>%;">
                            </div>
                        </div>
                        <p class="mt-2 fw-bold"><?php echo $progress; ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bi bi-list-check text-primary"></i> Вправи</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($exercises as $ex): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-secondary me-2"><?php echo $ex['order_num'] + 1; ?></span>
                                    <strong><?php echo htmlspecialchars($ex['name']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-tag"></i> <?php echo $ex['muscle_group']; ?>
                                        <span class="mx-1">•</span>
                                        <i class="bi bi-arrow-repeat"></i> <?php echo $ex['sets']; ?>×<?php echo $ex['reps']; ?>
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-<?php echo $ex['is_completed'] ? 'success' : 'secondary'; ?> rounded-pill">
                                        <?php echo $ex['is_completed'] ? '✅ Виконано' : '⏳ Очікує'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>