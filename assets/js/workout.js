// Трекер тренировок
function initWorkoutTracker() {
    const tracker = document.getElementById('workoutTracker');
    if (!tracker) return;
    
    // Таймер тренировки
    startWorkoutTimer();
    
    // Переключение упражнений
    const exercises = document.querySelectorAll('.exercise-item');
    exercises.forEach(exercise => {
        exercise.addEventListener('click', function() {
            toggleExercise(this);
        });
    });
    
    // Обработка завершения тренировки
    const completeBtn = document.getElementById('completeWorkout');
    if (completeBtn) {
        completeBtn.addEventListener('click', function() {
            completeWorkout();
        });
    }
}

// Таймер тренировки
function startWorkoutTimer() {
    const timerDisplay = document.getElementById('workoutTimer');
    if (!timerDisplay) return;
    
    let seconds = 0;
    let isRunning = true;
    
    const timer = setInterval(() => {
        if (!isRunning) return;
        
        seconds++;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        timerDisplay.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        
        // Обновление прогресса
        updateWorkoutProgress();
    }, 1000);
    
    // Пауза при скрытии страницы (оптимизация)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            isRunning = false;
        } else {
            isRunning = true;
        }
    });
}

// Обновление прогресса тренировки
function updateWorkoutProgress() {
    const exercises = document.querySelectorAll('.exercise-item');
    const total = exercises.length;
    let completed = 0;
    
    exercises.forEach(ex => {
        if (ex.classList.contains('completed')) {
            completed++;
        }
    });
    
    const progress = total > 0 ? Math.round((completed / total) * 100) : 0;
    
    const progressBar = document.getElementById('workoutProgress');
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.textContent = progress + '%';
    }
    
    const progressText = document.getElementById('progressText');
    if (progressText) {
        progressText.textContent = `${completed} з ${total} виконано`;
    }
}

// Переключение упражнения
function toggleExercise(element) {
    element.classList.toggle('completed');
    
    const checkbox = element.querySelector('input[type="checkbox"]');
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
    }
    
    updateWorkoutProgress();
    
    // Звук выполнения
    if (element.classList.contains('completed')) {
        playSound('complete');
    }
}

// Звуковые эффекты
function playSound(type) {
    // Простой вариант - использование Web Audio API
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        if (type === 'complete') {
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        }
    } catch(e) {
        // Звук не поддерживается
    }
}

// Завершение тренировки
function completeWorkout() {
    const totalExercises = document.querySelectorAll('.exercise-item').length;
    const completed = document.querySelectorAll('.exercise-item.completed').length;
    
    if (completed < totalExercises) {
        if (!confirm('Ви виконали не всі вправи. Завершити тренування?')) {
            return;
        }
    }
    
    // Отправка данных
    const formData = new FormData();
    formData.append('action', 'complete');
    formData.append('workout_id', document.getElementById('workoutId').value);
    formData.append('completed', completed);
    formData.append('total', totalExercises);
    
    fetch('/api/workout.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        let data;
        try {
            data = text ? JSON.parse(text) : {};
        } catch (error) {
            throw new Error('Сервер повернув некоректну відповідь');
        }

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Не вдалося завершити тренування');
        }

        showToast('🎉 Тренування завершено!', 'success');
        setTimeout(() => {
            window.location.href = '/dashboard.php?page=workouts&status=completed';
        }, 1200);
    })
    .catch(error => {
        showToast(error.message || 'Помилка з’єднання', 'danger');
        console.error('Workout complete error:', error);
    });
}

// Добавление упражнения в план
function addExerciseToPlan(exerciseId) {
    const formData = new FormData();
    formData.append('action', 'add_exercise');
    formData.append('exercise_id', exerciseId);
    formData.append('plan_id', document.getElementById('planId').value);
    
    fetch('/api/workout-plan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Вправу додано!', 'success');
            // Обновление списка
            location.reload();
        }
    })
    .catch(error => console.error('Ошибка:', error));
}

// Удаление упражнения из плана
function removeExerciseFromPlan(exerciseId) {
    if (!confirm('Видалити цю вправу?')) return;
    
    const formData = new FormData();
    formData.append('action', 'remove_exercise');
    formData.append('exercise_id', exerciseId);
    formData.append('plan_id', document.getElementById('planId').value);
    
    fetch('/api/workout-plan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Вправу видалено', 'info');
            location.reload();
        }
    })
    .catch(error => console.error('Ошибка:', error));
}