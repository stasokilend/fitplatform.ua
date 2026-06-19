// admin/js/stats.js

let rolesChart = null;
let activityChart = null;

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
});

async function loadStats() {
    const check = await apiRequest('admin/check.php', 'GET');
    if (!check.success) {
        showToast('Доступ заборонено', 'error');
        setTimeout(() => window.location.href = '../dashboard.html', 1000);
        return;
    }

    const result = await apiRequest('admin/get-stats.php', 'GET');
    if (!result.success) {
        showToast('Помилка завантаження статистики', 'error');
        return;
    }

    const stats = result.stats;

    document.getElementById('statTotalUsers').textContent = stats.total_users || 0;
    document.getElementById('statTrainers').textContent = stats.total_trainers || 0;
    document.getElementById('statAdmins').textContent = stats.total_admins || 0;
    document.getElementById('statTotalSessions').textContent = stats.total_sessions || 0;

    // Графік ролей
    const ctx1 = document.getElementById('rolesChart').getContext('2d');
    if (rolesChart) rolesChart.destroy();
    rolesChart = new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Користувачі', 'Тренери', 'Адміністратори'],
            datasets: [{
                data: [stats.total_users || 0, stats.total_trainers || 0, stats.total_admins || 0],
                backgroundColor: ['#0d6efd', '#ffc107', '#dc3545'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Графік активності (останні 7 днів)
    const ctx2 = document.getElementById('activityChart').getContext('2d');
    if (activityChart) activityChart.destroy();

    // Отримуємо тренування за останні 7 днів
    const sessionsResult = await apiRequest('../api/get-recent-sessions.php?days=7', 'GET');
    const sessions = sessionsResult.success ? sessionsResult.sessions : [];

    const days = [];
    const counts = [];
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const key = d.toISOString().split('T')[0];
        days.push(d.toLocaleDateString('uk-UA', { weekday: 'short', day: 'numeric' }));
        counts.push(sessions.filter(s => s.date === key).length);
    }

    activityChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: days,
            datasets: [{
                label: 'Тренувань',
                data: counts,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
}