// Основной файл инициализации
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    console.log('FitPlatform загружен!');
    
    // Инициализация всех модулей
    initAuth();
    initDashboard();
    initNotifications();
    initTooltips();
    
    // Обработка закрытия алертов
    autoCloseAlerts();
});

// Инициализация тултипов Bootstrap
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Автоматическое закрытие уведомлений через 5 секунд
function autoCloseAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.click();
            }
        }, 5000);
    });
}

// Форматирование чисел
function formatNumber(num, decimals = 0) {
    return Number(num).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

// Получение параметров из URL
function getUrlParams() {
    const params = {};
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    for (const [key, value] of urlParams) {
        params[key] = value;
    }
    return params;
}

// Дебаунс для оптимизации событий
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}