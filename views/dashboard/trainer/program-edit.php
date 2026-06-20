<?php
require_once 'config/database.php';

$programId = (int)($_GET['id'] ?? 0);
if (!$programId) {
    header('Location: /dashboard.php?page=programs');
    exit;
}

// Получаем данные программы (только для владельца)
$stmt = $pdo->prepare("SELECT * FROM trainer_programs WHERE id = ? AND trainer_id = ?");
$stmt->execute([$programId, $_SESSION['user_id']]);
$program = $stmt->fetch();

if (!$program) {
    header('Location: /dashboard.php?page=programs');
    exit;
}

// Получаем упражнения программы
$stmt = $pdo->prepare("
    SELECT pe.*, e.name, e.muscle_group, e.difficulty
    FROM program_exercises pe
    JOIN exercises e ON pe.exercise_id = e.id
    WHERE pe.program_id = ?
    ORDER BY pe.day, pe.order_num
");
$stmt->execute([$programId]);
$programExercises = $stmt->fetchAll();

// Получаем все упражнения для добавления
$stmt = $pdo->query("SELECT id, name, muscle_group, difficulty FROM exercises WHERE is_active = 1 ORDER BY muscle_group, name");
$allExercises = $stmt->fetchAll();

// Группировка по группам мышц
$groupedExercises = [];
foreach ($allExercises as $ex) {
    $groupedExercises[$ex['muscle_group']][] = $ex;
}

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
            <i class="bi bi-pencil text-primary"></i> Редагування програми
        </h1>
        <div class="d-flex gap-2">
            <a href="/dashboard.php?page=program-detail&id=<?php echo $program['id']; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-eye"></i> Перегляд
            </a>
            <a href="/dashboard.php?page=programs" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="programEditForm">
                        <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Назва програми</label>
                            <input type="text" id="programName" class="form-control" 
                                   value="<?php echo htmlspecialchars($program['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Опис</label>
                            <textarea id="programDescription" class="form-control" rows="4"><?php echo htmlspecialchars($program['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Складність</label>
                                <select id="programDifficulty" class="form-select">
                                    <option value="beginner" <?php echo $program['difficulty'] === 'beginner' ? 'selected' : ''; ?>>Початківець</option>
                                    <option value="intermediate" <?php echo $program['difficulty'] === 'intermediate' ? 'selected' : ''; ?>>Середній</option>
                                    <option value="advanced" <?php echo $program['difficulty'] === 'advanced' ? 'selected' : ''; ?>>Просунутий</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Тривалість (тижні)</label>
                                <input type="number" id="programDuration" class="form-control" 
                                       value="<?php echo $program['duration_weeks']; ?>" min="1" max="24">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Тренувань на тиждень</label>
                                <input type="number" id="programSessions" class="form-control" 
                                       value="<?php echo $program['sessions_per_week']; ?>" min="1" max="7">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Публічна</label>
                                <select id="programPublic" class="form-select">
                                    <option value="0" <?php echo !$program['is_public'] ? 'selected' : ''; ?>>Ні</option>
                                    <option value="1" <?php echo $program['is_public'] ? 'selected' : ''; ?>>Так</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Статус</label>
                                <select id="programActive" class="form-select">
                                    <option value="1" <?php echo $program['is_active'] ? 'selected' : ''; ?>>Активна</option>
                                    <option value="0" <?php echo !$program['is_active'] ? 'selected' : ''; ?>>Архівна</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6 class="fw-semibold"><i class="bi bi-dumbbell text-primary"></i> Вправи в програмі</h6>
                            <div id="exerciseList" class="mb-3">
                                <!-- Список добавленных упражнений -->
                            </div>
                            
                            <div class="input-group">
                                <select id="exerciseSelect" class="form-select">
                                    <option value="">-- Виберіть вправу --</option>
                                    <?php foreach ($groupedExercises as $group => $items): ?>
                                        <optgroup label="<?php echo $group; ?>">
                                            <?php foreach ($items as $ex): ?>
                                                <option value="<?php echo $ex['id']; ?>">
                                                    <?php echo htmlspecialchars($ex['name']); ?> 
                                                    (<?php echo $difficultyLabels[$ex['difficulty']]; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" id="addExerciseBtn">
                                    <i class="bi bi-plus"></i> Додати
                                </button>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Натисніть <strong>✏️</strong> для редагування параметрів вправи
                            </small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-gradient w-100 mt-4" id="saveProgramBtn">
                            <i class="bi bi-save"></i> Зберегти зміни
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bi bi-eye text-primary"></i> Попередній перегляд</h5>
                </div>
                <div class="card-body" id="previewContainer" style="max-height: 600px; overflow-y: auto;">
                    <!-- Будет заполнено JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== МОДАЛЬНОЕ ОКНО ДЛЯ РЕДАКТИРОВАНИЯ УПРАЖНЕНИЯ ===== -->
<div class="modal fade" id="exerciseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil text-primary"></i> Редагувати вправу
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exerciseModalForm">
                    <input type="hidden" id="editExerciseIndex" value="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Вправа</label>
                        <p class="form-control-static fw-bold" id="editExerciseName">-</p>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">День тренування</label>
                            <select id="editExerciseDay" class="form-select">
                                <?php for ($i = 1; $i <= 7; $i++): ?>
                                    <option value="<?php echo $i; ?>">День <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Підходи</label>
                            <input type="number" id="editExerciseSets" class="form-control" min="1" max="10" value="3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Повторення</label>
                            <input type="number" id="editExerciseReps" class="form-control" min="1" max="50" value="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Відпочинок (сек)</label>
                            <input type="number" id="editExerciseRest" class="form-control" min="10" max="300" value="60">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Нотатки до вправи</label>
                            <textarea id="editExerciseNotes" class="form-control" rows="2" placeholder="Техніка виконання, поради..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" id="saveExerciseChanges">
                    <i class="bi bi-save"></i> Зберегти
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let addedExercises = [];
let exerciseIdCounter = 0;
let currentEditIndex = -1;

document.addEventListener('DOMContentLoaded', function() {
    // ===== ЗАГРУЗКА СУЩЕСТВУЮЩИХ УПРАЖНЕНИЙ =====
    <?php foreach ($programExercises as $ex): ?>
        addedExercises.push({
            id: <?php echo $ex['id']; ?>,
            exercise_id: <?php echo $ex['exercise_id']; ?>,
            name: '<?php echo addslashes($ex['name']); ?>',
            muscle_group: '<?php echo addslashes($ex['muscle_group']); ?>',
            difficulty: '<?php echo addslashes($ex['difficulty']); ?>',
            day: <?php echo $ex['day']; ?>,
            sets: <?php echo $ex['sets']; ?>,
            reps: <?php echo $ex['reps']; ?>,
            rest_seconds: <?php echo $ex['rest_seconds']; ?>,
            notes: '<?php echo addslashes($ex['notes'] ?? ''); ?>',
            order_num: <?php echo $ex['order_num']; ?>
        });
    <?php endforeach; ?>
    
    renderExerciseList();
    updatePreview();
    
    // ===== ДОБАВЛЕНИЕ УПРАЖНЕНИЯ =====
    document.getElementById('addExerciseBtn').addEventListener('click', function() {
        const select = document.getElementById('exerciseSelect');
        const exerciseId = select.value;
        
        if (!exerciseId) {
            showToast('Виберіть вправу', 'warning');
            return;
        }
        
        const option = select.options[select.selectedIndex];
        const name = option.text.split(' (')[0];
        const difficulty = option.text.match(/\((.+)\)/)?.[1] || '';
        const muscleGroup = option.parentElement.label || 'Інше';
        
        if (addedExercises.some(e => e.exercise_id == exerciseId)) {
            showToast('Ця вправа вже додана', 'warning');
            return;
        }
        
        addedExercises.push({
            id: ++exerciseIdCounter,
            exercise_id: parseInt(exerciseId),
            name: name,
            muscle_group: muscleGroup,
            difficulty: difficulty,
            day: 1,
            sets: 3,
            reps: 10,
            rest_seconds: 60,
            notes: '',
            order_num: addedExercises.length
        });
        
        renderExerciseList();
        updatePreview();
        showToast('Вправу додано!', 'success');
    });
    
    // ===== УДАЛЕНИЕ УПРАЖНЕНИЯ =====
    window.removeExercise = function(index) {
        if (!confirm('Видалити цю вправу?')) return;
        addedExercises.splice(index, 1);
        renderExerciseList();
        updatePreview();
    };
    
    // ===== РЕДАКТИРОВАНИЕ УПРАЖНЕНИЯ (ОТКРЫТИЕ МОДАЛЬНОГО ОКНА) =====
    window.editExercise = function(index) {
        const ex = addedExercises[index];
        currentEditIndex = index;
        
        document.getElementById('editExerciseIndex').value = index;
        document.getElementById('editExerciseName').textContent = ex.name;
        document.getElementById('editExerciseDay').value = ex.day;
        document.getElementById('editExerciseSets').value = ex.sets;
        document.getElementById('editExerciseReps').value = ex.reps;
        document.getElementById('editExerciseRest').value = ex.rest_seconds;
        document.getElementById('editExerciseNotes').value = ex.notes || '';
        
        const modal = new bootstrap.Modal(document.getElementById('exerciseModal'));
        modal.show();
    };
    
    // ===== СОХРАНЕНИЕ ИЗМЕНЕНИЙ В УПРАЖНЕНИИ =====
    document.getElementById('saveExerciseChanges').addEventListener('click', function() {
        const index = parseInt(document.getElementById('editExerciseIndex').value);
        if (index < 0 || index >= addedExercises.length) return;
        
        addedExercises[index].day = parseInt(document.getElementById('editExerciseDay').value) || 1;
        addedExercises[index].sets = parseInt(document.getElementById('editExerciseSets').value) || 3;
        addedExercises[index].reps = parseInt(document.getElementById('editExerciseReps').value) || 10;
        addedExercises[index].rest_seconds = parseInt(document.getElementById('editExerciseRest').value) || 60;
        addedExercises[index].notes = document.getElementById('editExerciseNotes').value || '';
        
        renderExerciseList();
        updatePreview();
        
        // Закрываем модальное окно
        const modal = bootstrap.Modal.getInstance(document.getElementById('exerciseModal'));
        modal.hide();
        
        showToast('Вправу оновлено!', 'success');
    });
    
    // ===== СОХРАНЕНИЕ ПРОГРАММЫ =====
    document.getElementById('programEditForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (addedExercises.length === 0) {
            showToast('Додайте хоча б одну вправу', 'warning');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'update_program');
        formData.append('program_id', <?php echo $program['id']; ?>);
        formData.append('name', document.getElementById('programName').value);
        formData.append('description', document.getElementById('programDescription').value);
        formData.append('difficulty', document.getElementById('programDifficulty').value);
        formData.append('duration_weeks', document.getElementById('programDuration').value);
        formData.append('sessions_per_week', document.getElementById('programSessions').value);
        formData.append('is_public', document.getElementById('programPublic').value);
        formData.append('is_active', document.getElementById('programActive').value);
        formData.append('exercises', JSON.stringify(addedExercises));
        
        const btn = document.getElementById('saveProgramBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Збереження...';
        
        fetch('/api/trainer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Зберегти зміни';
            
            if (data.success) {
                showToast('Програму оновлено! 🎉', 'success');
                setTimeout(() => {
                    window.location.href = '/dashboard.php?page=program-detail&id=<?php echo $program['id']; ?>';
                }, 1500);
            } else {
                showToast(data.error || 'Помилка оновлення', 'danger');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Зберегти зміни';
            showToast('Помилка з\'єднання', 'danger');
        });
    });
});

// ===== ФУНКЦИИ =====
function renderExerciseList() {
    const container = document.getElementById('exerciseList');
    
    if (addedExercises.length === 0) {
        container.innerHTML = '<div class="text-muted small">Вправи не додані</div>';
        return;
    }
    
    let html = '<div class="list-group">';
    addedExercises.forEach((ex, index) => {
        const difficultyColor = ex.difficulty === 'advanced' ? 'danger' : (ex.difficulty === 'intermediate' ? 'warning' : 'success');
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-secondary">${index + 1}</span>
                            <strong>${ex.name}</strong>
                            <span class="badge bg-${difficultyColor}">${ex.difficulty}</span>
                            <span class="badge bg-primary">День ${ex.day}</span>
                            <span class="badge bg-info">${ex.sets}×${ex.reps}</span>
                            <span class="badge bg-secondary">${ex.rest_seconds}с</span>
                        </div>
                        ${ex.notes ? `<div class="small text-muted mt-1"><i class="bi bi-pencil"></i> ${ex.notes}</div>` : ''}
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <button class="btn btn-sm btn-outline-primary" onclick="editExercise(${index})" title="Редагувати">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeExercise(${index})" title="Видалити">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function updatePreview() {
    const container = document.getElementById('previewContainer');
    const name = document.getElementById('programName').value || 'Нова програма';
    const difficulty = document.getElementById('programDifficulty').value;
    const duration = document.getElementById('programDuration').value || 4;
    const sessions = document.getElementById('programSessions').value || 3;
    const description = document.getElementById('programDescription').value || '';
    
    const difficultyLabels = {
        'beginner': 'Початківець',
        'intermediate': 'Середній',
        'advanced': 'Просунутий'
    };
    
    const difficultyColors = {
        'beginner': 'success',
        'intermediate': 'warning',
        'advanced': 'danger'
    };
    
    let html = `
        <h6 class="fw-bold">${htmlspecialchars(name)}</h6>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-${difficultyColors[difficulty]}">${difficultyLabels[difficulty]}</span>
            <span class="badge bg-secondary">${duration} тиж.</span>
            <span class="badge bg-info">${sessions} трен./тиж.</span>
            <span class="badge bg-primary">${addedExercises.length} вправ</span>
        </div>
    `;
    
    if (description) {
        html += `
            <div class="mb-3 p-3 bg-light rounded-3" style="font-size: 0.9rem;">
                ${htmlspecialchars(description)}
            </div>
        `;
    }
    
    if (addedExercises.length > 0) {
        // Группировка по дням
        const days = {};
        addedExercises.forEach(ex => {
            const day = ex.day || 1;
            if (!days[day]) days[day] = [];
            days[day].push(ex);
        });
        
        html += '<h6 class="mt-3"><i class="bi bi-list-check text-primary"></i> Вправи:</h6>';
        Object.keys(days).sort((a, b) => a - b).forEach(day => {
            html += `<div class="mt-2"><small class="fw-semibold text-muted">День ${day}:</small>`;
            html += '<div class="list-group list-group-flush">';
            days[day].forEach((ex, idx) => {
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span><span class="badge bg-secondary me-2">${idx + 1}</span> ${ex.name}</span>
                        <span class="badge bg-${difficultyColors[ex.difficulty] || 'secondary'}">${ex.sets}×${ex.reps}</span>
                    </div>
                `;
            });
            html += '</div></div>';
        });
    } else {
        html += '<div class="text-muted small">Вправи не додані</div>';
    }
    
    container.innerHTML = html;
}

function htmlspecialchars(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Live preview при изменении полей
document.querySelectorAll('#programName, #programDifficulty, #programDuration, #programSessions, #programDescription').forEach(function(el) {
    el.addEventListener('input', updatePreview);
    el.addEventListener('change', updatePreview);
});
</script>