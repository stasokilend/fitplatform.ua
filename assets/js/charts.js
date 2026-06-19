// Визуализация данных с Chart.js (исправленная версия)
function initCharts() {
    // Проверяем загрузку Chart.js
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js не загружен');
        return;
    }
    
    // Глобальная настройка Chart.js для CSP
    Chart.defaults.plugins.tooltip = {
        enabled: true
    };
    
    // Отключаем скрытые функции, которые используют eval
    Chart.overrides = Chart.overrides || {};
    Chart.overrides.global = Chart.overrides.global || {};
    Chart.overrides.global.plugins = Chart.overrides.global.plugins || {};
    Chart.overrides.global.plugins.legend = Chart.overrides.global.plugins.legend || {};
    
    const chartContainers = document.querySelectorAll('.chart-container');
    chartContainers.forEach(container => {
        const type = container.dataset.chartType || 'line';
        let data = {};
        
        try {
            data = JSON.parse(container.dataset.chartData || '{}');
        } catch(e) {
            data = {};
        }
        
        switch(type) {
            case 'line':
                createLineChart(container, data);
                break;
            case 'bar':
                createBarChart(container, data);
                break;
            case 'doughnut':
                createDoughnutChart(container, data);
                break;
            case 'radar':
                createRadarChart(container, data);
                break;
        }
    });
}

// Линейный график (прогресс) - безопасная версия
function createLineChart(container, data) {
    const ctx = container.querySelector('canvas').getContext('2d');
    
    // Безопасная проверка данных
    const labels = Array.isArray(data.labels) ? data.labels : ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер'];
    const values = Array.isArray(data.values) ? data.values : [0, 10, 15, 25, 30, 45];
    const label = data.label || 'Прогрес';
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: values,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Столбчатый график - безопасная версия
function createBarChart(container, data) {
    const ctx = container.querySelector('canvas').getContext('2d');
    
    const labels = Array.isArray(data.labels) ? data.labels : ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];
    const values = Array.isArray(data.values) ? data.values : [10, 25, 15, 30, 20, 35, 5];
    const label = data.label || 'Активність';
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: values,
                backgroundColor: '#0d6efd',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Круговая диаграмма - безопасная версия
function createDoughnutChart(container, data) {
    const ctx = container.querySelector('canvas').getContext('2d');
    
    const defaultData = {
        labels: ['Вправи', 'Кардіо', 'Розминка', 'Відпочинок'],
        values: [40, 30, 20, 10],
        colors: ['#0d6efd', '#198754', '#ffc107', '#6c757d']
    };
    
    const chartLabels = Array.isArray(data.labels) ? data.labels : defaultData.labels;
    const chartValues = Array.isArray(data.values) ? data.values : defaultData.values;
    const chartColors = Array.isArray(data.colors) ? data.colors : defaultData.colors;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartLabels,
            datasets: [{
                data: chartValues,
                backgroundColor: chartColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Радарная диаграмма - безопасная версия
function createRadarChart(container, data) {
    const ctx = container.querySelector('canvas').getContext('2d');
    
    const labels = Array.isArray(data.labels) ? data.labels : ['Сила', 'Витривалість', 'Гнучкість', 'Швидкість', 'Координація'];
    const values = Array.isArray(data.values) ? data.values : [70, 50, 40, 60, 55];
    const label = data.label || 'Поточний рівень';
    
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: values,
                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                borderColor: '#0d6efd',
                pointBackgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                r: {
                    min: 0,
                    max: 100
                }
            }
        }
    });
}