// js/workout.js

let currentSessionId = null;
let currentPlanId = null;
let exerciseIndex = 0;
let exercises = [];
let isResting = false;
let timerInterval = null;
let sessionStartTime = null;

document.addEventListener('DOMContentLoaded', function() {
    // Генерація плану на дашборді
    const generateBtn = document.getElementById('generateWorkoutBtn');
    if (generateBtn) {
        generateBtn.addEventListener('click', generateWorkout);
    }

    // Перехід на сторінку тренування
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('start-workout-btn')) {
            const planId = e.target.dataset.planId;
            window.location.href = appUrl(`workout.php?plan_id=${planId}`);
        }
    });

    // Ініціалізація сторінки тренування
    if (window.location.pathname.includes('workout.php')) {
        initWorkoutPage();
    }
});

// Генерація тренування
async function generateWorkout() {
    const btn = document.getElementById('generateWorkoutBtn');
    const status = document.getElementById('generationStatus');
    const container = document.getElementById('currentPlanContainer');

    disableButton(btn, 'Генерація...');
    status.textContent = 'Аналіз профілю та підбір вправ...';

    try {
        const result = await apiRequest('generate-workout.php', 'GET');

        if (!result.success) {
            status.textContent = '❌ ' + (result.error || 'Помилка генерації');
            enableButton(btn);
            return;
        }

        const workout = result.workout;
        currentPlanId = workout.plan_id;
        exercises = workout.exercises;

        status.textContent = '✅ План згенеровано! ' + workout.total_duration + ' хв, ~' + workout.total_calories + ' ккал';
        enableButton(btn);
        renderPlan(workout, container);

    } catch (e) {
        status.textContent = '❌ Помилка з\'єднання з сервером';
        enableButton(btn);
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
        showToast('План не знайдено', 'error');
        setTimeout(() => window.location.href = appUrl('dashboard.php'), 1000);
        return;
    }

    try {
        const result = await apiRequest(`get-plan.php?plan_id=${planId}`, 'GET');
        if (!result.success) {
            showToast('Помилка завантаження плану', 'error');
            setTimeout(() => window.location.href = appUrl('dashboard.php'), 1000);
            return;
        }
        exercises = result.plan.exercises;
        currentPlanId = planId;
        setupWorkout();
    } catch (e) {
        showToast('Помилка з\'єднання', 'error');
        setTimeout(() => window.location.href = appUrl('dashboard.php'), 1000);
    }

    // Кнопки керування
    document.getElementById('startExerciseBtn').addEventListener('click', startExercise);
    document.getElementById('restBtn').addEventListener('click', startRest);
    document.getElementById('finishWorkoutBtn').addEventListener('click', finishWorkout);
    document.getElementById('sendHrBtn').addEventListener('click', sendHeartRate);

    // Автоматичне надсилання пульсу (кожні 5 секунд, якщо введено)
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

    sessionStartTime = Date.now();
    setInterval(updateSessionTimer, 1000);

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

    document.getElementById('exerciseProgress').style.width = '100%';
    document.getElementById('exerciseTimer').textContent = '0с';

    document.getElementById('startExerciseBtn').disabled = false;
    document.getElementById('restBtn').disabled = true;
}

function startExercise() {
    const btn = document.getElementById('startExerciseBtn');
    btn.disabled = true;

    const ex = exercises[exerciseIndex];
    let timeLeft = ex.duration_minutes * 60;
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
            showToast('Підхід завершено! Відпочиньте.', 'info', 2000);
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
                showToast('Наступна вправа: ' + exercises[exerciseIndex].name, 'info', 2000);
            } else {
                finishWorkout();
            }
        }
    }, 1000);
}

async function sendHeartRate() {
    const input = document.getElementById('heartRateInput');
    const bpm = parseInt(input.value);
    if (!bpm || bpm < 30 || bpm > 220) return;

    if (!currentSessionId) {
        showToast('Спочатку розпочніть тренування', 'warning');
        return;
    }

    const result = await apiRequest('update-hr.php', 'POST', {
        session_id: currentSessionId,
        bpm: bpm
    });

    if (result.success) {
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
        const zoneLabels = {
            'low': 'Низька',
            'normal': 'Оптимальна',
            'high': 'Висока',
            'danger': 'Небезпечна!'
        };

        indicator.style.backgroundColor = zoneColors[zone] || '#6c757d';
        zoneText.textContent = zoneLabels[zone] || 'Невідома';
        recText.textContent = 'Рекомендація: ' + (result.recommendation || 'Продовжуйте');
        
        if (zone === 'danger') {
            showToast('⚠️ Увага! Пульс занадто високий! Зменште інтенсивність.', 'error', 3000);
        }
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
        showToast('Тренування ще не розпочато', 'warning');
        return;
    }

    const totalMinutes = Math.floor((Date.now() - sessionStartTime) / 60000);
    if (totalMinutes < 1) {
        showToast('Тренування занадто коротке', 'warning');
        return;
    }

    const confirmFinish = confirm('Завершити тренування?');
    if (!confirmFinish) return;

    const btn = document.getElementById('finishWorkoutBtn');
    disableButton(btn, 'Завершення...');

    const result = await apiRequest('finish-session.php', 'POST', {
        session_id: currentSessionId,
        duration_minutes: totalMinutes,
        calories_burned: Math.round(totalMinutes * 5)
    });

    if (result.success) {
        showToast('🎉 Тренування завершено! Відмінна робота!', 'success', 4000);
        setTimeout(() => window.location.href = appUrl('stats.php'), 1500);
    } else {
        enableButton(btn);
        showToast('Помилка збереження', 'error');
    }
}