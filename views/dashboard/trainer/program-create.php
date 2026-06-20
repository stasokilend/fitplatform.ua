<?php
require_once 'config/database.php';

// Получаем все упражнения
$stmt = $pdo->query("SELECT id, name, muscle_group, difficulty FROM exercises WHERE is_active = 1 ORDER BY muscle_group, name");
$exercises = $stmt->fetchAll();

// Группировка по группам мышц
$groupedExercises = [];
foreach ($exercises as $ex) {
    $groupedExercises[$ex['muscle_group']][] = $ex;
}
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-plus-circle text-primary"></i> Нова програма
        </h1>
        <a href="/dashboard.php?page=programs" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="programForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Назва програми</label>
                            <input type="text" id="programName" class="form-control" placeholder="Наприклад: Силова програма для початківців" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Опис</label>
                            <textarea id="programDescription" class="form-control" rows="3" placeholder="Опишіть програму, її цілі та особливості"></textarea>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Складність</label>
                                <select id="programDifficulty" class="form-select">
                                    <option value="beginner">Початківець</option>
                                    <option value="intermediate" selected>Середній</option>
                                    <option value="advanced">Просунутий</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Тривалість (тижні)</label>
                                <input type="number" id="programDuration" class="form-control" value="4" min="1" max="24">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Тренувань на тиждень</label>
                                <input type="number" id="programSessions" class="form-control" value="3" min="1" max="7">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Публічна</label>
                                <select id="programPublic" class="form-select">
                                    <option value="0">Ні</option>
                                    <option value="1">Так</option>
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
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-gradient w-100 mt-4" id="saveProgramBtn">
                            <i class="bi bi-save"></i> Зберегти програму
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bi bi-info-circle text-primary"></i> Попередній перегляд</h5>
                </div>
                <div class="card-body" id="previewContainer">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-eye display-4 d-block mb-3"></i>
                        <p>Додайте вправи та заповніть форму для попереднього перегляду</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let addedExercises = [];

document.addEventListener('DOMContentLoaded', function() {
    // Добавление упражнения
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
        
        // Проверяем, не добавлена ли уже
        if (addedExercises.some(e => e.id == exerciseId)) {
            showToast('Ця вправа вже додана', 'warning');
            return;
        }
        
        addedExercises.push({
            id: exerciseId,
            name: name,
            difficulty: difficulty
        });
        
        renderExerciseList();
        updatePreview();
    });
    
    // Удаление упражнения
    window.removeExercise = function(index) {
        addedExercises.splice(index, 1);
        renderExerciseList();
        updatePreview();
    };
    
    // Сохранение программы
    document.getElementById('programForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (addedExercises.length === 0) {
            showToast('Додайте хоча б одну вправу', 'warning');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'create_program');
        formData.append('name', document.getElementById('programName').value);
        formData.append('description', document.getElementById('programDescription').value);
        formData.append('difficulty', document.getElementById('programDifficulty').value);
        formData.append('duration_weeks', document.getElementById('programDuration').value);
        formData.append('sessions_per_week', document.getElementById('programSessions').value);
        formData.append('is_public', document.getElementById('programPublic').value);
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
            btn.innerHTML = '<i class="bi bi-save"></i> Зберегти програму';
            
            if (data.success) {
                showToast('Програму створено! 🎉', 'success');
                setTimeout(() => {
                    window.location.href = '/dashboard.php?page=programs';
                }, 1500);
            } else {
                showToast(data.error || 'Помилка створення', 'danger');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Зберегти програму';
            showToast('Помилка з\'єднання', 'danger');
        });
    });
});

function renderExerciseList() {
    const container = document.getElementById('exerciseList');
    
    if (addedExercises.length === 0) {
        container.innerHTML = '<div class="text-muted small">Вправи не додані</div>';
        return;
    }
    
    let html = '<div class="list-group">';
    addedExercises.forEach((ex, index) => {
        html += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                    <span class="badge bg-secondary me-2">${index + 1}</span>
                    ${ex.name}
                    <small class="text-muted">(${ex.difficulty})</small>
                </span>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExercise(${index})">
                    <i class="bi bi-x"></i>
                </button>
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
    
    if (addedExercises.length > 0) {
        html += '<div class="list-group list-group-flush">';
        addedExercises.forEach((ex, index) => {
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                    <span><span class="badge bg-secondary me-2">${index + 1}</span> ${ex.name}</span>
                    <span class="badge bg-${difficultyColors[ex.difficulty] || 'secondary'}">${ex.difficulty}</span>
                </div>
            `;
        });
        html += '</div>';
    } else {
        html += '<div class="text-muted small">Вправи не додані</div>';
    }
    
    container.innerHTML = html;
}

// Функция для безопасного вывода HTML
function htmlspecialchars(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Live preview при изменении полей
document.querySelectorAll('#programName, #programDifficulty, #programDuration, #programSessions').forEach(function(el) {
    el.addEventListener('input', updatePreview);
    el.addEventListener('change', updatePreview);
});
</script>