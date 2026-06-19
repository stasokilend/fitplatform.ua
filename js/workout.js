// js/workout.js

let currentSessionId = null;
let currentPlanId = null;
let exerciseIndex = 0;
let exercises = [];
let isResting = false;
let timerInterval = null;
let hrInterval = null;
let sessionStartTime = null;

document.addEventListener('DOMContentLoaded', function() {
    // Генерація плану на дашборді
    const generateBtn = document.getElementById('generateWorkoutBtn');
    if (generateBtn) {
        generateBtn.addEventListener('click', generateWorkout);
    }

    // Перехід на сторінку тренування при кліку на "Почати"
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('start-workout-btn')) {
            const planId = e.target.dataset.planId;
            window.location.href = `workout.html?plan_id=${planId}`;
        }
    });

    // Ініціалізація сторінки тренування
    if (window.location.pathname.includes('workout.html')) {
        initWorkoutPage();
    }
});

// Генерація тренування на дашборді
async function generateWorkout() {
    const btn = document.getElementById('generateWorkoutBtn');
    const status = document.getElementById('generationStatus');
    const container = document.getElementById('currentPlanContainer');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Генерація...';
    status.textContent = 'Аналіз профілю та підбір вправ...';

    try {
        const result = await apiRequest('generate-workout.php', 'GET');

        if (!result.success) {
            status.textContent = '❌ ' + (result.error || 'Помилка генерації');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync me-2"></i>Згенерувати план';
            return;
        }

        const workout = result.workout;
        currentPlanId = workout.plan_id;
        exercises = workout.exercises;

        status.textContent = '✅ План згенеровано! ' + workout.total_duration + ' хв, ~' + workout.total_calories + ' ккал';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync me-2"></i>Згенерувати новий план';

        // Відображення плану
        renderPlan(workout, container);

    } catch (e) {
        status.textContent = '❌ Помилка з\'єднання з сервером';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync me-2"></i>Згенерувати план';
    }
}

function renderPlan(workout, container) {
    let html = `
        <div class="list-group">
            <div class="list-group-item bg-light d-flex justify-content-between align-items-center">
                <span><strong>Тривалість:</strong> ${workout.total_duration} хв</span>
                <span><strong>Калорії:</strong> ~${workout.total_calories}</span>
            </div>
    `;

    workout.exercises.forEach((ex, idx) => {
        html += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-secondary me-2">${idx+1}</span>
                    <strong>${ex.name}</strong>
                    <small class="text-muted d-block">${ex.sets}×${ex.reps} · ${ex.rest_seconds}с відпочинку</small>
                </div>
                <span class="badge bg-info">${ex.duration_minutes} хв</span>
            </div>
        `;
    });

    html += `
            <div class="list-group-item">
                <button class="btn btn-success w-100 start-workout-btn" data-plan-id="${workout.plan_id}">
                    <i class="fas fa-play me-2"></i>Почати тренування
                </button>
            </div>
        </div>
    `;

    container.innerHTML = html;
}

// ========== СТОРІНКА ТРЕНУВАННЯ ==========

async function initWorkoutPage() {
    const params = new URLSearchParams(window.location.search);
    const planId = params.get('plan_id');

    if (!planId) {
        alert('План не знайдено');
        window.location.href = 'dashboard.html';
        return;
    }

    // Завантажити план з БД (через API)
    try {
        const result = await apiRequest(`get-plan.php?plan_id=${planId}`, 'GET');
        if (!result.success) {
            alert('Помилка завантаження плану');
            window.location.href = 'dashboard.html';
            return;
        }
        exercises = result.plan.exercises;
        currentPlanId = planId;
        setupWorkout();
    } catch (e) {
        alert('Помилка з\'єднання');
        window.location.href = 'dashboard.html';
    }

    // Кнопки керування
    document.getElementById('startExerciseBtn').addEventListener('click', startExercise);
    document.getElementById('restBtn').addEventListener('click', startRest);
    document.getElementById('finishWorkoutBtn').addEventListener('click', finishWorkout);
    document.getElementById('sendHrBtn').addEventListener('click', sendHeartRate);

    // Автоматичне надсилання пульсу (імітація кожні 5 секунд)
    // Для реального використання - інтеграція з трекером
    setInterval(() => {
        const input = document.getElementById('heartRateInput');
        if (input.value && parseInt(input.value) > 0) {
            sendHeartRate();
        }
    }, 5000);
}

function setupWorkout() {
    document.getElementById('totalExercises').textContent = exercises.length;
    exerciseIndex = 0;
    showExercise(exerciseIndex);

    // Запуск таймера сесії
    sessionStartTime = Date.now();
    setInterval(updateSessionTimer, 1000);

    // Створення сесії в БД
    apiRequest('start-session.php', 'POST', { plan_id: currentPlanId })
        .then(res => {
            if (res.success) currentSessionId = res.session_id;
        });
}

function showExercise(index) {
    if (index >= exercises.length) {
        finishWorkout();
        return;
    }

    const ex = exercises[index];
    document.getElementById('exerciseName').textContent = ex.name;
    document.getElementById('currentExerciseIndex').textContent = index + 1;
    document.getElementById('exerciseDescription').textContent = ex.description || 'Виконайте вправу з правильною технікою';
    document.getElementById('exerciseSets').textContent = ex.sets;
    document.getElementById('exerciseReps').textContent = ex.reps;
    document.getElementById('exerciseRest').textContent = ex.rest_seconds + 'с';

    // Скидання прогресу
    document.getElementById('exerciseProgress').style.width = '100%';
    document.getElementById('exerciseTimer').textContent = '0с';

    // Активувати кнопки
    document.getElementById('startExerciseBtn').disabled = false;
    document.getElementById('restBtn').disabled = true;
}

function startExercise() {
    const btn = document.getElementById('startExerciseBtn');
    btn.disabled = true;

    const ex = exercises[exerciseIndex];
    let timeLeft = ex.duration_minutes * 60; // секунд
    const progressBar = document.getElementById('exerciseProgress');
    const timerDisplay = document.getElementById('exerciseTimer');
    const totalTime = timeLeft;

    if (timerInterval) clearInterval(timerInterval);

    timerInterval = setInterval(() => {
        timeLeft--;
        const percent = (timeLeft / totalTime) * 100;
        progressBar.style.width = Math.max(0, percent) + '%';
        timerDisplay.textContent = Math.floor(timeLeft) + 'с';

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            progressBar.style.width = '0%';
            timerDisplay.textContent = 'Готово!';
            document.getElementById('restBtn').disabled = false;
            document.getElementById('startExerciseBtn').disabled = true;
        }
    }, 1000);
}

function startRest() {
    if (timerInterval) clearInterval(timerInterval);
    const restTime = exercises[exerciseIndex].rest_seconds;
    let timeLeft = restTime;
    const progressBar = document.getElementById('exerciseProgress');
    const timerDisplay = document.getElementById('exerciseTimer');

    document.getElementById('restBtn').disabled = true;

    timerInterval = setInterval(() => {
        timeLeft--;
        const percent = (timeLeft / restTime) * 100;
        progressBar.style.width = Math.max(0, percent) + '%';
        timerDisplay.textContent = 'Відпочинок ' + Math.floor(timeLeft) + 'с';

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            progressBar.style.width = '100%';
            timerDisplay.textContent = 'Далі!';
            exerciseIndex++;
            if (exerciseIndex < exercises.length) {
                showExercise(exerciseIndex);
            } else {
                finishWorkout();
            }
        }
    }, 1000);
}

async function sendHeartRate() {
    const input = document.getElementById('heartRateInput');
    const bpm = parseInt(input.value);
    if (!bpm || bpm < 30 || bpm > 220) {
        return;
    }

    if (!currentSessionId) {
        alert('Спочатку розпочніть тренування');
        return;
    }

    const result = await apiRequest('update-hr.php', 'POST', {
        session_id: currentSessionId,
        bpm: bpm
    });

    if (result.success) {
        // Оновлення індикатора зони
        const zone = result.zone;
        const indicator = document.getElementById('hrZoneIndicator');
        const zoneText = document.getElementById('hrZoneText');
        const recText = document.getElementById('hrRecommendation');

        const zoneColors = {
            'low': '#0d6efd',
            'normal': '#198754',
            'high': '#ffc107',
            'danger': '#dc3545'
        };
        indicator.style.backgroundColor = zoneColors[zone] || '#6c757d';
        zoneText.textContent = zone === 'low' ? 'Низька' :
                               zone === 'normal' ? 'Оптимальна' :
                               zone === 'high' ? 'Висока' : 'Небезпечна!';
        recText.textContent = 'Рекомендація: ' + (result.recommendation || 'Продовжуйте');
    }
}

function updateSessionTimer() {
    if (!sessionStartTime) return;
    const elapsed = Math.floor((Date.now() - sessionStartTime) / 1000);
    const mins = String(Math.floor(elapsed / 60)).padStart(2, '0');
    const secs = String(elapsed % 60).padStart(2, '0');
    document.getElementById('sessionTimer').textContent = mins + ':' + secs;
}

async function finishWorkout() {
    if (timerInterval) clearInterval(timerInterval);

    if (!currentSessionId) {
        alert('Тренування ще не розпочато');
        return;
    }

    const totalMinutes = Math.floor((Date.now() - sessionStartTime) / 60000);
    const confirmFinish = confirm('Завершити тренування?');
    if (!confirmFinish) return;

    const result = await apiRequest('finish-session.php', 'POST', {
        session_id: currentSessionId,
        duration_minutes: totalMinutes,
        calories_burned: Math.round(totalMinutes * 5) // приблизно 5 ккал/хв
    });

    if (result.success) {
        alert('Тренування завершено! Відмінна робота! 🎉');
        window.location.href = 'stats.html';
    } else {
        alert('Помилка збереження');
    }
}