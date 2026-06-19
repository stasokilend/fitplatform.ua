// js/dashboard.js

let progressChart = null;

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    loadRecentWorkouts();
    loadProgressChart();
});

async function loadDashboardData() {
    // Профіль
    const profileResult = await apiRequest('profile.php', 'GET');
    if (profileResult.success && profileResult.profile) {
        const p = profileResult.profile;
        document.getElementById('userName').textContent = p.full_name || 'Користувач';
        document.getElementById('userEmail').textContent = p.email || '';
        
        // Аватар
        const initials = (p.full_name || 'U').split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
        document.getElementById('avatarDisplay').textContent = initials || 'U';
        
        // Рівень
        const levels = ['', 'Початківець', 'Середній', 'Просунутий'];
        document.getElementById('userLevel').textContent = levels[p.fitness_level] || 'Початківець';
        document.getElementById('userBmr').textContent = 'BMR: ' + Math.round(p.bmr || 0) + ' ккал';
    }

    // Статистика
    const statsResult = await apiRequest('get-stats.php', 'GET');
    if (statsResult.success) {
        const total = statsResult.stats.total || {};
        document.getElementById('miniSessions').textContent = total.total_sessions || 0;
        document.getElementById('miniMinutes').textContent = total.total_minutes || 0;
        document.getElementById('miniCalories').textContent = Math.round(total.total_calories || 0);
        
        // Стрік (дні поспіль)
        const recent = statsResult.stats.recent || [];
        let streak = 0;
        if (recent.length > 0) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            for (let i = 0; i < recent.length; i++) {
                const date = new Date(recent[i].started_at);
                date.setHours(0, 0, 0, 0);
                const diff = Math.floor((today - date) / (1000 * 60 * 60 * 24));
                if (diff === i) {
                    streak++;
                } else {
                    break;
                }
            }
        }
        document.getElementById('miniStreak').textContent = streak;
    }
}

async function loadRecentWorkouts() {
    const result = await apiRequest('get-stats.php', 'GET');
    if (!result.success) return;

    const recent = result.stats.recent || [];
    const container = document.getElementById('recentWorkouts');

    if (recent.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-3">Немає тренувань. Почніть займатися!</p>';
        return;
    }

    container.innerHTML = recent.slice(0, 5).map(s => `
        <div class="workout-list-item">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${s.duration_minutes || 0} хв</strong>
                    <span class="text-muted ms-2">${Math.round(s.calories_burned || 0)} ккал</span>
                </div>
                <span class="date">${new Date(s.started_at).toLocaleDateString('uk-UA')}</span>
            </div>
            <div class="small text-muted">Пульс: ${s.avg_hr || 0} bpm</div>
        </div>
    `).join('');
}

async function loadProgressChart() {
    const result = await apiRequest('get-stats.php', 'GET');
    if (!result.success) return;

    const recent = result.stats.recent || [];
    const ctx = document.getElementById('progressChart').getContext('2d');

    if (recent.length === 0) {
        document.getElementById('noProgressData').classList.remove('d-none');
        return;
    }

    const last7 = recent.slice(0, 7).reverse();
    const labels = last7.map(s => new Date(s.started_at).toLocaleDateString('uk-UA'));
    const durations = last7.map(s => s.duration_minutes || 0);

    if (progressChart) progressChart.destroy();

    progressChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Хвилин',
                data: durations,
                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}