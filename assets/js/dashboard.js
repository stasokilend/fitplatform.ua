// Интерактивность кабинета
function initDashboard() {
    // Обновление данных в реальном времени (каждые 30 секунд)
    if (document.querySelector('.dashboard-stats')) {
        setInterval(refreshStats, 30000);
    }
    
    // Анимация счетчиков
    animateCounters();
    
    // Обработка кликов по карточкам
    const cards = document.querySelectorAll('.clickable-card');
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const url = this.dataset.url;
            if (url) {
                window.location.href = url;
            }
        });
    });
    
    // Обработка кнопки "Показать еще"
    const showMoreBtn = document.getElementById('showMoreWorkouts');
    if (showMoreBtn) {
        showMoreBtn.addEventListener('click', function() {
            loadMoreWorkouts(this);
        });
    }
}

// Анимация счетчиков
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.dataset.target);
        const duration = 1000;
        const step = Math.max(1, Math.floor(target / 60));
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current >= target) {
                counter.textContent = target;
                return;
            }
            counter.textContent = current;
            requestAnimationFrame(updateCounter);
        };
        
        // Запуск анимации при видимости элемента
        if (isElementVisible(counter)) {
            updateCounter();
        } else {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCounter();
                        observer.unobserve(counter);
                    }
                });
            });
            observer.observe(counter);
        }
    });
}

// Проверка видимости элемента
function isElementVisible(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Обновление статистики (AJAX)
function refreshStats() {
    const container = document.querySelector('.dashboard-stats');
    if (!container) return;
    
    fetch('/api/stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStats(data);
            }
        })
        .catch(error => console.error('Ошибка обновления:', error));
}

// Обновление UI статистики
function updateStats(data) {
    const elements = {
        totalWorkouts: document.getElementById('totalWorkouts'),
        completedWorkouts: document.getElementById('completedWorkouts'),
        totalCalories: document.getElementById('totalCalories'),
        currentWeight: document.getElementById('currentWeight')
    };
    
    if (elements.totalWorkouts) {
        animateNumber(elements.totalWorkouts, elements.totalWorkouts.textContent, data.total_workouts);
    }
    if (elements.completedWorkouts) {
        animateNumber(elements.completedWorkouts, elements.completedWorkouts.textContent, data.completed_workouts);
    }
    if (elements.totalCalories) {
        animateNumber(elements.totalCalories, elements.totalCalories.textContent, data.total_calories);
    }
}

// Анимация числа
function animateNumber(element, from, to) {
    const duration = 500;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = Math.round(from + (to - from) * progress);
        
        if (typeof to === 'number') {
            element.textContent = current.toLocaleString();
        } else {
            element.textContent = current;
        }
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Загрузка дополнительных тренировок
function loadMoreWorkouts(button) {
    const container = document.getElementById('workoutsContainer');
    const page = parseInt(button.dataset.page || 1);
    
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Завантаження...';
    
    fetch(`/api/workouts.php?page=${page + 1}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.workouts.length > 0) {
                data.workouts.forEach(workout => {
                    container.insertAdjacentHTML('beforeend', createWorkoutCard(workout));
                });
                button.dataset.page = page + 1;
                button.disabled = false;
                button.innerHTML = 'Показати ще';
            } else {
                button.disabled = true;
                button.innerHTML = 'Більше немає тренувань';
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            button.disabled = false;
            button.innerHTML = 'Показати ще';
        });
}

// Создание карточки тренировки
function createWorkoutCard(workout) {
    return `
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 workout-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-dumbbell text-primary"></i>
                        ${workout.name}
                    </h5>
                    <div class="mb-2">
                        <span class="badge bg-${workout.status === 'completed' ? 'success' : 'warning'}">
                            ${workout.status === 'completed' ? '✅ Завершено' : '⏳ В процесі'}
                        </span>
                    </div>
                    <p class="card-text small text-muted">
                        <i class="bi bi-clock"></i> ${workout.duration} хв
                        <br>
                        <i class="bi bi-calendar"></i> ${workout.date}
                    </p>
                    <div class="progress mb-2" style="height: 5px;">
                        <div class="progress-bar bg-${workout.progress >= 100 ? 'success' : 'primary'}" 
                             style="width: ${workout.progress}%"></div>
                    </div>
                    <small class="text-muted">Прогрес: ${workout.progress}%</small>
                </div>
                <div class="card-footer bg-transparent">
                    <button class="btn btn-sm btn-outline-primary w-100" onclick="viewWorkout(${workout.id})">
                        <i class="bi bi-eye"></i> Переглянути
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Просмотр тренировки
function viewWorkout(id) {
    // Здесь будет открытие модального окна с деталями
    alert(`Просмотр тренировки ID: ${id}`);
    // Можно открыть модальное окно или перейти на страницу
    // window.location.href = `/workout.php?id=${id}`;
}

// Фильтрация тренировок
function filterWorkouts(status) {
    const cards = document.querySelectorAll('.workout-card');
    cards.forEach(card => {
        const badge = card.querySelector('.badge');
        if (!badge) return;
        
        if (status === 'all' || badge.textContent.includes(status === 'completed' ? 'Завершено' : 'В процесі')) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Обновление активных кнопок
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.querySelector(`.filter-btn[data-status="${status}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
}