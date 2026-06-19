// js/stats.js

let durationChart = null;
let caloriesChart = null;

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
});

async function loadStats() {
    const result = await apiRequest('get-stats.php', 'GET');
    if (!result.success) {
        showToast('Помилка завантаження статистики', 'error');
        return;
    }

    const stats = result.stats;
    const total = stats.total || {};

    document.getElementById('totalSessions').textContent = total.total_sessions || 0;
    document.getElementById('totalMinutes').textContent = total.total_minutes || 0;
    document.getElementById('avgHeartRate').textContent = Math.round(total.avg_hr || 0);
    document.getElementById('totalCalories').textContent = Math.round(total.total_calories || 0);

    const recent = stats.recent || [];
    const historyBody = document.getElementById('historyBody');
    if (recent.length === 0) {
        historyBody.innerHTML = '<tr><td colspan="4" class="text-center">Немає даних. Почніть тренування!</td></tr>';
    } else {
        historyBody.innerHTML = recent.map(s => `
            <tr>
                <td>${new Date(s.started_at).toLocaleDateString('uk-UA')}</td>
                <td>${s.duration_minutes || 0} хв</td>
                <td>${s.avg_hr || 0}</td>
                <td>${Math.round(s.calories_burned || 0)}</td>
            </tr>
        `).join('');
    }

    const labels = recent.map(s => new Date(s.started_at).toLocaleDateString('uk-UA')).reverse();
    const durations = recent.map(s => s.duration_minutes || 0).reverse();
    const calories = recent.map(s => Math.round(s.calories_burned || 0)).reverse();

    if (labels.length > 0) {
        createCharts(labels, durations, calories);
    } else {
        showToast('Немає даних для графіків. Виконайте тренування!', 'info', 3000);
    }
}

function createCharts(labels, durations, calories) {
    const ctx1 = document.getElementById('durationChart').getContext('2d');
    if (durationChart) durationChart.destroy();
    durationChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Тривалість (хв)',
                data: durations,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    const ctx2 = document.getElementById('caloriesChart').getContext('2d');
    if (caloriesChart) caloriesChart.destroy();
    caloriesChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Спалені калорії',
                data: calories,
                backgroundColor: 'rgba(220, 53, 69, 0.2)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}