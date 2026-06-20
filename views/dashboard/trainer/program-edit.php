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
                                                    (<?php echo $ex['difficulty']; ?>)
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
                                Для каждого упражнения можно указать день, количество подходов и повторений
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

<script>
let addedExercises = [];
let exerciseIdCounter = 0;

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
    
    // ===== РЕДАКТИРОВАНИЕ УПРАЖНЕНИЯ =====
    window.editExercise = function(index) {
        const ex = addedExercises[index];
        // Простое редактирование через prompt
        const newDay = prompt('День тренировки (1-7):', ex.day);
        if (newDay !== null) {
            ex.day = parseInt(newDay) || 1;
        }
        const newSets = prompt('Количество подходов:', ex.sets);
        if (newSets !== null) {
            ex.sets = parseInt(newSets) || 3;
        }
        const newReps = prompt('Количество повторений:', ex.reps);
        if (newReps !== null) {
            ex.reps = parseInt(newReps) || 10;
        }
        const newRest = prompt('Время отдыха (сек):', ex.rest_seconds);
        if (newRest !== null) {
            ex.rest_seconds = parseInt(newRest) || 60;
        }
        const newNotes = prompt('Заметки к упражнению:', ex.notes || '');
        if (newNotes !== null) {
            ex.notes = newNotes;
        }
        renderExerciseList();
        updatePreview();
    };
    
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
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">${index + 1}</span>
                            <strong>${ex.name}</strong>
                            <span class="badge bg-${ex.difficulty === 'advanced' ? 'danger' : (ex.difficulty === 'intermediate' ? 'warning' : 'success')}">${ex.difficulty}</span>
                            <span class="badge bg-primary">День ${ex.day}</span>
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="bi bi-arrow-repeat"></i> ${ex.sets}×${ex.reps}
                            <span class="mx-2">•</span>
                            <i class="bi bi-clock"></i> ${ex.rest_seconds} с
                            ${ex.notes ? `<span class="mx-2">•</span> <i class="bi bi-pencil"></i> ${ex.notes}` : ''}
                        </div>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <button class="btn btn-sm btn-outline-primary" onclick="editExercise(${index})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeExercise(${index})">
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