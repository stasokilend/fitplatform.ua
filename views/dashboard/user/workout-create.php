<?php
require_once 'config/database.php';
$userId = $_SESSION['user_id'];

// Получаем профиль для настроек
$stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

// Получаем количество упражнений в базе
$stmt = $pdo->query("SELECT COUNT(*) as total FROM exercises WHERE is_active = 1");
$exerciseCount = $stmt->fetch()['total'];
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-plus-circle text-primary"></i> Створити тренування
        </h1>
        <a href="/dashboard.php?page=workouts" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <?php if ($exerciseCount < 5): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            У базі даних мало вправ. Додайте більше вправ через адмін-панель.
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Форма создания -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-sliders2 text-primary"></i> Налаштування
                    </h5>
                </div>
                <div class="card-body">
                    <form id="generateForm" onsubmit="return false;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Назва тренування</label>
                            <input type="text" id="workoutName" class="form-control" 
                                   value="Моє тренування <?php echo date('d.m.Y'); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Тривалість (хв)</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="range" id="durationRange" class="form-range flex-grow-1" 
                                       min="10" max="60" step="5" value="30">
                                <span class="fw-bold fs-5" id="durationDisplay" style="min-width: 50px;">30</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Фокус тренування</label>
                            <div class="d-grid gap-2" id="focusButtons">
                                <button type="button" class="btn btn-outline-primary focus-btn active" data-focus="health">
                                    <i class="bi bi-heart-pulse"></i> Здоров'я
                                </button>
                                <button type="button" class="btn btn-outline-primary focus-btn" data-focus="weight_loss">
                                    <i class="bi bi-fire"></i> Зниження ваги
                                </button>
                                <button type="button" class="btn btn-outline-primary focus-btn" data-focus="muscle_gain">
                                    <i class="bi bi-dumbbell"></i> Набір маси
                                </button>
                                <button type="button" class="btn btn-outline-primary focus-btn" data-focus="endurance">
                                    <i class="bi bi-arrow-repeat"></i> Витривалість
                                </button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary btn-gradient w-100 py-2" id="generateBtn">
                            <i class="bi bi-magic"></i> Згенерувати тренування
                        </button>
                    </form>

                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Тренування генерується на основі ваших цілей, рівня підготовки 
                            та медичних обмежень
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Результат генерации -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" id="resultCard" style="display: none;">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check text-success"></i> Сгенероване тренування
                    </h5>
                    <span class="badge bg-primary" id="exerciseCount">0 вправ</span>
                </div>
                <div class="card-body" id="resultBody">
                    <!-- Результат будет вставлен сюда -->
                </div>
                <div class="card-footer bg-transparent border-0">
                    <div class="d-flex gap-2">
                        <button class="btn btn-success flex-grow-1" id="saveWorkoutBtn">
                            <i class="bi bi-save"></i> Зберегти тренування
                        </button>
                        <button class="btn btn-outline-secondary" id="regenerateBtn">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Скелетон загрузки -->
            <div id="loadingSkeleton" style="display: none;">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Завантаження...</span>
                            </div>
                            <p class="mt-3 text-muted">Генерація тренування...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Сообщение, если нет упражнений -->
            <div class="card border-0 shadow-sm" id="noExercisesCard" style="display: none;">
                <div class="card-body text-center py-5">
                    <i class="bi bi-emoji-frown display-1 text-muted d-block mb-3"></i>
                    <h5>Не вдалося підібрати вправи</h5>
                    <p class="text-muted">Спробуйте змінити параметри або додайте більше вправ у базу</p>
                    <button class="btn btn-outline-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-repeat"></i> Спробувати ще
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentResult = null;
    let selectedFocus = 'health';
    let isGenerating = false;
    
    // ---- КНОПКИ ФОКУСА ----
    const focusButtons = document.querySelectorAll('.focus-btn');
    const focusContainer = document.getElementById('focusButtons');
    
    // Обработчик для кнопок фокуса (делегирование)
    focusContainer.addEventListener('click', function(e) {
        const btn = e.target.closest('.focus-btn');
        if (!btn) return;
        
        // Убираем active у всех
        focusButtons.forEach(function(b) {
            b.classList.remove('active');
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline-primary');
        });
        
        // Добавляем active на нажатую
        btn.classList.add('active');
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary');
        
        selectedFocus = btn.dataset.focus;
        console.log('Выбран фокус:', selectedFocus);
    });
    
    // Убедимся, что первая кнопка активна при загрузке
    const firstBtn = document.querySelector('.focus-btn.active');
    if (firstBtn) {
        firstBtn.classList.remove('btn-outline-primary');
        firstBtn.classList.add('btn-primary');
    }
    
    // ---- ДИАПАЗОН ДЛИТЕЛЬНОСТИ ----
    const durationRange = document.getElementById('durationRange');
    const durationDisplay = document.getElementById('durationDisplay');
    
    durationRange.addEventListener('input', function() {
        durationDisplay.textContent = this.value;
    });
    
    // ---- ГЕНЕРАЦИЯ ТРЕНИРОВКИ ----
    const generateBtn = document.getElementById('generateBtn');
    
    generateBtn.addEventListener('click', function() {
        generateWorkout();
    });
    
    function generateWorkout() {
        if (isGenerating) return;
        isGenerating = true;
        
        const name = document.getElementById('workoutName').value.trim() || 'Моє тренування';
        const duration = parseInt(document.getElementById('durationRange').value);
        
        // Показываем загрузку
        document.getElementById('resultCard').style.display = 'none';
        document.getElementById('noExercisesCard').style.display = 'none';
        document.getElementById('loadingSkeleton').style.display = 'block';
        
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Генерація...';
        
        const formData = new FormData();
        formData.append('action', 'generate');
        formData.append('name', name);
        formData.append('duration', duration);
        formData.append('focus', selectedFocus);
        
        fetch('/api/workout-generate.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            isGenerating = false;
            document.getElementById('loadingSkeleton').style.display = 'none';
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="bi bi-magic"></i> Згенерувати тренування';
            
            if (data.success) {
                currentResult = data;
                showResult(data.workout);
                document.getElementById('resultCard').style.display = 'block';
                showToast('Тренування згенеровано!', 'success');
            } else {
                document.getElementById('noExercisesCard').style.display = 'block';
                showToast(data.error || 'Помилка генерації', 'danger');
            }
        })
        .catch(function(error) {
            isGenerating = false;
            document.getElementById('loadingSkeleton').style.display = 'none';
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="bi bi-magic"></i> Згенерувати тренування';
            showToast('Помилка з\'єднання з сервером: ' + error.message, 'danger');
            console.error('Ошибка:', error);
        });
    }
    
    function showResult(workout) {
        const body = document.getElementById('resultBody');
        document.getElementById('exerciseCount').textContent = workout.count + ' вправ';
        
        const focusLabels = {
            'health': 'Здоров\'я',
            'weight_loss': 'Зниження ваги',
            'muscle_gain': 'Набір маси',
            'endurance': 'Витривалість'
        };
        
        let html = `
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <span class="badge bg-primary">${focusLabels[workout.focus] || workout.focus}</span>
                </div>
                <div>
                    <span class="text-muted">
                        <i class="bi bi-clock"></i> ${workout.total_duration} хв
                        <span class="mx-2">•</span>
                        <i class="bi bi-fire"></i> ${workout.total_calories} ккал
                    </span>
                </div>
            </div>
            <div class="list-group">
        `;
        
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
        
        workout.exercises.forEach(function(ex, index) {
            const sets = ex.sets || 3;
            const reps = ex.reps || 10;
            
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-secondary me-2">${index + 1}</span>
                        <strong>${ex.name}</strong>
                        <br>
                        <small class="text-muted">
                            <i class="bi bi-tag"></i> ${ex.muscle_group}
                            <span class="mx-1">•</span>
                            <span class="badge bg-${difficultyColors[ex.difficulty] || 'secondary'}">${difficultyLabels[ex.difficulty] || ex.difficulty}</span>
                        </small>
                    </div>
                    <div class="text-end">
                        <div>${sets}×${reps}</div>
                        <small class="text-muted">${ex.duration_min} хв</small>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        body.innerHTML = html;
    }
    
    // ---- СОХРАНЕНИЕ ТРЕНИРОВКИ ----
    document.getElementById('saveWorkoutBtn').addEventListener('click', function() {
        if (!currentResult || !currentResult.plan_id) {
            showToast('Спочатку згенеруйте тренування', 'warning');
            return;
        }
        
        const name = document.getElementById('workoutName').value.trim() || 'Моє тренування';
        
        const formData = new FormData();
        formData.append('action', 'save');
        formData.append('name', name);
        formData.append('plan_id', currentResult.plan_id);
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Збереження...';
        
        fetch('/api/workout-generate.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-save"></i> Зберегти тренування';
            
            if (data.success) {
                showToast('Тренування збережено! 🎉', 'success');
                setTimeout(function() {
                    window.location.href = '/dashboard.php?page=workout-track&id=' + currentResult.plan_id;
                }, 1500);
            } else {
                showToast(data.error || 'Помилка збереження', 'danger');
            }
        })
        .catch(function(error) {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-save"></i> Зберегти тренування';
            showToast('Помилка з\'єднання: ' + error.message, 'danger');
        });
    });
    
    // ---- ПОВТОРНАЯ ГЕНЕРАЦИЯ ----
    document.getElementById('regenerateBtn').addEventListener('click', function() {
        generateWorkout();
    });
});
</script>

<style>
.focus-btn {
    transition: all 0.3s ease;
    justify-content: center;
}

.focus-btn.active {
    background: var(--gradient-primary, linear-gradient(135deg, #6C63FF 0%, #FF6584 100%));
    color: #fff;
    border-color: #6C63FF;
}

.focus-btn.active i {
    color: #fff;
}

.focus-btn i {
    margin-right: 8px;
}

.focus-btn:hover {
    transform: translateY(-1px);
}
</style>