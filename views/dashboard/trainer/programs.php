<?php
require_once 'config/database.php';
require_once 'controllers/TrainerController.php';

$trainer = new TrainerController($userId);
$programs = $trainer->getPrograms();

// Получаем все упражнения для добавления
$stmt = $pdo->query("SELECT id, name, muscle_group FROM exercises WHERE is_active = 1 ORDER BY name");
$allExercises = $stmt->fetchAll();
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-file-text text-primary"></i> Програми тренувань
        </h1>
        <a href="/dashboard.php?page=program-create" class="btn btn-primary btn-gradient">
            <i class="bi bi-plus-circle"></i> Нова програма
        </a>
    </div>

    <?php if (count($programs) > 0): ?>
        <div class="row g-4">
            <?php foreach ($programs as $program): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($program['name']); ?></h5>
                                <span class="badge bg-<?php echo $program['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $program['is_active'] ? 'Активна' : 'Архівна'; ?>
                                </span>
                            </div>
                            <p class="card-text small text-muted">
                                <?php echo htmlspecialchars(substr($program['description'] ?? '', 0, 100)) . (strlen($program['description'] ?? '') > 100 ? '...' : ''); ?>
                            </p>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-<?php echo $program['difficulty'] === 'advanced' ? 'danger' : ($program['difficulty'] === 'intermediate' ? 'warning' : 'success'); ?>">
                                    <?php echo getFitnessLevelLabel($program['difficulty']); ?>
                                </span>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-calendar"></i> <?php echo $program['duration_weeks']; ?> тиж.
                                </span>
                                <span class="badge bg-info">
                                    <i class="bi bi-arrow-repeat"></i> <?php echo $program['sessions_per_week']; ?>/тиж.
                                </span>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-dumbbell"></i> <?php echo $program['exercises_count'] ?? 0; ?> вправ</span>
                                <span><i class="bi bi-people"></i> <?php echo $program['clients_count'] ?? 0; ?> клієнтів</span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <div class="d-flex gap-2">
                                <a href="/dashboard.php?page=program-detail&id=<?php echo $program['id']; ?>" 
                                class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="bi bi-eye"></i> Перегляд
                                </a>
                                <a href="/dashboard.php?page=program-edit&id=<?php echo $program['id']; ?>" 
                                class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteProgram(<?php echo $program['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-file-text display-4 d-block mb-3"></i>
            <h5>Ще немає програм</h5>
            <p>Створіть першу програму тренувань для своїх клієнтів</p>
            <a href="/dashboard.php?page=program-create" class="btn btn-primary btn-gradient">
                <i class="bi bi-plus-circle"></i> Створити програму
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteProgram(id) {
    if (!confirm('Ви впевнені, що хочете видалити цю програму?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_program');
    formData.append('program_id', id);
    
    fetch('/api/trainer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Програму видалено', 'success');
            location.reload();
        } else {
            showToast(data.error || 'Помилка видалення', 'danger');
        }
    });
}
</script>