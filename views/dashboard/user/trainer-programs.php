<?php
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

// Получаем публичные программы всех тренеров
$stmt = $pdo->prepare("
    SELECT tp.*, u.full_name as trainer_name, u.id as trainer_id,
           (SELECT COUNT(*) FROM program_exercises WHERE program_id = tp.id) as exercises_count
    FROM trainer_programs tp
    JOIN users u ON tp.trainer_id = u.id
    WHERE tp.is_public = 1 AND tp.is_active = 1
    ORDER BY tp.created_at DESC
");
$stmt->execute();
$programs = $stmt->fetchAll();

// Получаем программы, назначенные пользователю
$stmt = $pdo->prepare("
    SELECT cp.*, tp.name as program_name, tp.description, tp.difficulty, 
           tp.duration_weeks, tp.sessions_per_week, u.full_name as trainer_name
    FROM client_programs cp
    JOIN trainer_programs tp ON cp.program_id = tp.id
    JOIN users u ON tp.trainer_id = u.id
    WHERE cp.client_id = ? AND cp.status = 'active'
");
$stmt->execute([$userId]);
$myPrograms = $stmt->fetchAll();

$difficultyLabels = [
    'beginner' => 'Початківець',
    'intermediate' => 'Середній',
    'advanced' => 'Просунутий'
];

$difficultyColors = [
    'beginner' => 'success',
    'intermediate' => 'warning',
    'advanced' => 'danger'
];
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-file-text text-primary"></i> Програми тренерів
        </h1>
        <span class="text-muted">
            <i class="bi bi-globe"></i> Публічні програми
        </span>
    </div>

    <!-- Мои программы -->
    <?php if (count($myPrograms) > 0): ?>
        <div class="mb-4">
            <h5 class="mb-3">
                <i class="bi bi-check-circle text-success"></i> Мої програми
                <span class="badge bg-secondary ms-2"><?php echo count($myPrograms); ?></span>
            </h5>
            <div class="row g-3">
                <?php foreach ($myPrograms as $program): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($program['program_name']); ?></h6>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Активна
                                    </span>
                                </div>
                                <p class="card-text small text-muted">
                                    <?php echo htmlspecialchars(substr($program['description'] ?? '', 0, 100)) . (strlen($program['description'] ?? '') > 100 ? '...' : ''); ?>
                                </p>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-<?php echo $difficultyColors[$program['difficulty']]; ?>">
                                        <?php echo $difficultyLabels[$program['difficulty']]; ?>
                                    </span>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($program['trainer_name']); ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span><i class="bi bi-calendar-week"></i> <?php echo $program['duration_weeks']; ?> тиж.</span>
                                    <span><i class="bi bi-arrow-repeat"></i> <?php echo $program['sessions_per_week']; ?>/тиж.</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="/dashboard.php?page=trainer-program-detail&id=<?php echo $program['program_id']; ?>" 
                                   class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-eye"></i> Переглянути
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Все публичные программы -->
    <h5 class="mb-3">
        <i class="bi bi-globe text-primary"></i> Публічні програми
        <span class="badge bg-secondary ms-2"><?php echo count($programs); ?></span>
    </h5>

    <?php if (count($programs) > 0): ?>
        <div class="row g-4">
            <?php foreach ($programs as $program): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($program['name']); ?></h6>
                                <?php if ($program['trainer_id'] == $userId): ?>
                                    <span class="badge bg-primary">Моя</span>
                                <?php endif; ?>
                            </div>
                            <p class="card-text small text-muted">
                                <?php echo htmlspecialchars(substr($program['description'] ?? '', 0, 100)) . (strlen($program['description'] ?? '') > 100 ? '...' : ''); ?>
                            </p>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-<?php echo $difficultyColors[$program['difficulty']]; ?>">
                                    <?php echo $difficultyLabels[$program['difficulty']]; ?>
                                </span>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($program['trainer_name']); ?>
                                </span>
                                <span class="badge bg-info">
                                    <i class="bi bi-dumbbell"></i> <?php echo $program['exercises_count']; ?> вправ
                                </span>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-calendar-week"></i> <?php echo $program['duration_weeks']; ?> тиж.</span>
                                <span><i class="bi bi-arrow-repeat"></i> <?php echo $program['sessions_per_week']; ?>/тиж.</span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="/dashboard.php?page=trainer-program-detail&id=<?php echo $program['id']; ?>" 
                               class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-eye"></i> Переглянути
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-file-text display-4 d-block mb-3"></i>
            <h5>Немає публічних програм</h5>
            <p>Тренери ще не опублікували жодної програми</p>
        </div>
    <?php endif; ?>
</div>