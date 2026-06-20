<?php
require_once 'config/database.php';

$programId = (int)($_GET['id'] ?? 0);
if (!$programId) {
    header('Location: /dashboard.php?page=trainer-programs');
    exit;
}

// Получаем данные программы (только публичные)
$stmt = $pdo->prepare("
    SELECT tp.*, u.full_name as trainer_name, u.id as trainer_id
    FROM trainer_programs tp
    JOIN users u ON tp.trainer_id = u.id
    WHERE tp.id = ? AND tp.is_public = 1 AND tp.is_active = 1
");
$stmt->execute([$programId]);
$program = $stmt->fetch();

if (!$program) {
    header('Location: /dashboard.php?page=trainer-programs');
    exit;
}

// Получаем упражнения программы
$stmt = $pdo->prepare("
    SELECT pe.*, e.name, e.muscle_group, e.difficulty, e.description as exercise_description
    FROM program_exercises pe
    JOIN exercises e ON pe.exercise_id = e.id
    WHERE pe.program_id = ?
    ORDER BY pe.day, pe.order_num
");
$stmt->execute([$programId]);
$exercises = $stmt->fetchAll();

// Группировка по дням
$days = [];
foreach ($exercises as $ex) {
    $day = $ex['day'] ?? 1;
    if (!isset($days[$day])) {
        $days[$day] = [];
    }
    $days[$day][] = $ex;
}

// Проверяем, назначена ли программа пользователю
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT id FROM client_programs 
    WHERE client_id = ? AND program_id = ? AND status = 'active'
");
$stmt->execute([$userId, $programId]);
$isAssigned = $stmt->fetch();

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
        <div>
            <h1 class="h2">
                <i class="bi bi-file-text text-primary"></i> 
                <?php echo htmlspecialchars($program['name']); ?>
            </h1>
            <small class="text-muted">
                <i class="bi bi-person"></i> Автор: <?php echo htmlspecialchars($program['trainer_name']); ?>
                <span class="mx-2">•</span>
                <i class="bi bi-calendar"></i> <?php echo date('d.m.Y', strtotime($program['created_at'])); ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <?php if ($isAssigned): ?>
                <span class="badge bg-success fs-6 px-3 py-2 d-flex align-items-center">
                    <i class="bi bi-check-circle me-2"></i> Призначено
                </span>
            <?php else: ?>
                <button class="btn btn-primary btn-gradient" id="requestProgramBtn">
                    <i class="bi bi-plus-circle"></i> Запросити програму
                </button>
            <?php endif; ?>
            <a href="/dashboard.php?page=trainer-programs" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Информация о программе -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-<?php echo $difficultyColors[$program['difficulty']]; ?> fs-6 px-3 py-2">
                            <i class="bi bi-bar-chart"></i> <?php echo $difficultyLabels[$program['difficulty']]; ?>
                        </span>
                        <span class="badge bg-secondary fs-6 px-3 py-2">
                            <i class="bi bi-calendar-week"></i> <?php echo $program['duration_weeks']; ?> тижнів
                        </span>
                        <span class="badge bg-info fs-6 px-3 py-2">
                            <i class="bi bi-arrow-repeat"></i> <?php echo $program['sessions_per_week']; ?> трен./тиж.
                        </span>
                        <span class="badge bg-primary fs-6 px-3 py-2">
                            <i class="bi bi-dumbbell"></i> <?php echo count($exercises); ?> вправ
                        </span>
                    </div>
                    
                    <?php if ($program['description']): ?>
                        <div class="program-description mt-3">
                            <h6 class="fw-semibold"><i class="bi bi-info-circle text-primary"></i> Опис</h6>
                            <div class="p-3 bg-light rounded-3">
                                <?php echo $program['description']; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold"><i class="bi bi-person text-primary"></i> Про тренера</h6>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 48px; height: 48px; font-size: 1.2rem; flex-shrink: 0;">
                            <?php echo strtoupper(substr($program['trainer_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($program['trainer_name']); ?></div>
                            <small class="text-muted">Тренер</small>
                        </div>
                    </div>
                    
                    <?php if ($program['trainer_id'] != $userId && !$isAssigned): ?>
                        <hr>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-gradient" id="requestProgramBtn">
                                <i class="bi bi-plus-circle"></i> Запросити програму
                            </button>
                            <small class="text-muted text-center">Тренер розгляне ваш запит</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Упражнения по дням -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0">
                <i class="bi bi-list-check text-primary"></i> Програма тренувань
            </h5>
        </div>
        <div class="card-body">
            <?php if (count($days) > 0): ?>
                <div class="accordion" id="daysAccordion">
                    <?php foreach ($days as $dayNum => $dayExercises): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?php echo $dayNum > 1 ? 'collapsed' : ''; ?>" 
                                        type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#day<?php echo $dayNum; ?>">
                                    <span class="fw-semibold">
                                        <i class="bi bi-calendar-day text-primary"></i> 
                                        День <?php echo $dayNum; ?>
                                    </span>
                                    <span class="badge bg-secondary ms-2"><?php echo count($dayExercises); ?> вправ</span>
                                </button>
                            </h2>
                            <div id="day<?php echo $dayNum; ?>" 
                                 class="accordion-collapse collapse <?php echo $dayNum == 1 ? 'show' : ''; ?>" 
                                 data-bs-parent="#daysAccordion">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Вправа</th>
                                                    <th>Група м'язів</th>
                                                    <th>Підходи</th>
                                                    <th>Повторення</th>
                                                    <th>Складність</th>
                                                    <th>Відпочинок</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dayExercises as $index => $ex): ?>
                                                    <tr>
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($ex['name']); ?></strong>
                                                            <?php if ($ex['notes']): ?>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($ex['notes']); ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($ex['muscle_group']); ?></td>
                                                        <td><?php echo $ex['sets']; ?></td>
                                                        <td><?php echo $ex['reps']; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $difficultyColors[$ex['difficulty']]; ?>">
                                                                <?php echo $difficultyLabels[$ex['difficulty']]; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $ex['rest_seconds']; ?> с</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                    <p>У цій програмі ще немає вправ</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('requestProgramBtn')?.addEventListener('click', function() {
    if (!confirm('Надіслати запит тренеру на цю програму?')) return;
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Відправка...';
    
    const formData = new FormData();
    formData.append('action', 'request_program');
    formData.append('program_id', <?php echo $programId; ?>);
    
    fetch('/api/trainer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-plus-circle"></i> Запросити програму';
        
        if (data.success) {
            showToast('Запит надіслано тренеру!', 'success');
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Запит надіслано';
            btn.className = 'btn btn-success';
            btn.disabled = true;
        } else {
            showToast(data.error || 'Помилка відправки запиту', 'danger');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-plus-circle"></i> Запросити програму';
        showToast('Помилка з\'єднання', 'danger');
    });
});
</script>