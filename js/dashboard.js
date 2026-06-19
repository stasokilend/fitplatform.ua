// js/dashboard.js - Логіка дашборда

document.addEventListener('DOMContentLoaded', function() {
    loadNav().then(() => {
        updateNavVisibility();
        loadDashboardStats();
        loadLastSession();
        loadTopExercises();
        updateUserInfo();
    });
});

async function loadDashboardStats() {
    const result = await apiRequest('dashboard-stats.php', 'GET');
    if (!result.success) {
        showToast('Помилка завантаження статистики', 'error');
        return;
    }

    const stats = result.stats;
    document.getElementById('statTotalSessions').textContent = stats.total_sessions || 0;
    document.getElementById('statTotalMinutes').textContent = stats.total_minutes || 0;
    document.getElementById('statWeekSessions').textContent = stats.sessions_week || 0;
    document.getElementById('statTotalCalories').textContent = Math.round(stats.total_calories || 0);

    if (stats.active_session) {
        showToast('У вас є незавершене тренування. Продовжіть у розділі "Мої тренування"', 'info', 5000);
    }
}

async function loadLastSession() {
    const result = await apiRequest('dashboard-stats.php', 'GET');
    if (!result.success) return;

    const lastSession = result.stats.last_session;
    const container = document.getElementById('lastSessionText');

    if (!lastSession) {
        container.textContent = 'Немає даних. Почніть тренування!';
        return;
    }

    const date = new Date(lastSession.started_at).toLocaleDateString('uk-UA');
    container.innerHTML = `
        <div><strong>Дата:</strong> ${date}</div>
        <div><strong>Тривалість:</strong> ${lastSession.duration_minutes || 0} хв</div>
        <div><strong>Калорії:</strong> ${Math.round(lastSession.calories_burned || 0)}</div>
    `;
}

async function loadTopExercises() {
    const result = await apiRequest('dashboard-stats.php', 'GET');
    if (!result.success) return;

    const exercises = result.stats.top_exercises || [];
    const container = document.getElementById('topExercisesContainer');

    if (exercises.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Виконайте тренування, щоб побачити статистику</p>';
        return;
    }

    const colors = ['#ffd700', '#c0c0c0', '#cd7f32'];
    container.innerHTML = `
        <div class="row g-2">
            ${exercises.map((ex, i) => `
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <span style="color: ${colors[i]}; font-size: 1.2rem;">🏆</span>
                            <span>${ex.name}</span>
                        </div>
                        <span class="badge bg-secondary">${ex.count}×</span>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function updateUserInfo() {
    fetch(API_BASE + 'profile.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.profile) {
                const name = data.profile.full_name || 'Користувач';
                document.getElementById('userName').textContent = name;
                document.getElementById('welcomeMessage').innerHTML = `👋 Вітаємо, <span id="userName">${name}</span>!`;
            }
        })
        .catch(() => {});
}