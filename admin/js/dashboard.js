// admin/js/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    loadAdminDashboard();
});

async function loadAdminDashboard() {
    // Перевірка прав
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

    // Оновлення карток
    document.getElementById('statUsers').textContent = stats.total_users || 0;
    document.getElementById('statExercises').textContent = stats.total_exercises || 0;
    document.getElementById('statSessions').textContent = stats.total_sessions || 0;
    document.getElementById('statMinutes').textContent = stats.total_minutes || 0;

    // Останні користувачі
    const usersHtml = stats.recent_users.map(u => `
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
                <strong>${u.full_name}</strong>
                <span class="text-muted small d-block">${u.email}</span>
            </div>
            <span class="badge bg-secondary">${new Date(u.created_at).toLocaleDateString('uk-UA')}</span>
        </div>
    `).join('');
    document.getElementById('recentUsers').innerHTML = usersHtml || '<p class="text-muted">Немає даних</p>';

    // Останні тренування
    const sessionsHtml = stats.recent_sessions.map(s => `
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
                <strong>${s.full_name}</strong>
                <span class="text-muted small d-block">${s.duration_minutes || 0} хв · ${s.calories_burned || 0} ккал</span>
            </div>
            <span class="badge bg-info">${new Date(s.started_at).toLocaleDateString('uk-UA')}</span>
        </div>
    `).join('');
    document.getElementById('recentSessions').innerHTML = sessionsHtml || '<p class="text-muted">Немає даних</p>';
}