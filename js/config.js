// js/config.js

const API_BASE = '/api/';

// === TOAST СИСТЕМА ===
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    const toast = document.createElement('div');
    toast.className = `toast-custom ${type}`;
    toast.innerHTML = `
        <span class="toast-icon"><i class="fas ${icons[type] || icons.info}"></i></span>
        <span class="toast-message">${message}</span>
        <span class="toast-close">&times;</span>
    `;

    container.appendChild(toast);

    // Показати з анімацією
    setTimeout(() => toast.classList.add('show'), 10);

    // Закриття по кнопці
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    });

    // Автоматичне зникнення
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// === ПЕРЕВІРКА АВТОРИЗАЦІЇ ===
function checkAuth() {
    fetch(API_BASE + 'profile.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                window.location.href = 'login.html';
            } else {
                // Якщо профіль не заповнений (немає ваги, віку тощо) – редірект на налаштування
                const profile = data.profile;
                if (!profile || !profile.weight || !profile.age) {
                    window.location.href = 'profile-setup.html';
                }
            }
        })
        .catch(() => {
            window.location.href = 'login.html';
        });
}

// === ВИХІД ===
function logout() {
    fetch(API_BASE + 'logout.php', { method: 'POST' })
        .then(() => {
            showToast('Ви вийшли з аккаунту', 'info');
            setTimeout(() => window.location.href = 'login.html', 500);
        })
        .catch(() => {
            window.location.href = 'login.html';
        });
}

// === ЗАГАЛЬНИЙ ЗАПИТ ДО API ===
function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: { 'Content-Type': 'application/json' },
    };
    if (data) options.body = JSON.stringify(data);
    return fetch(API_BASE + url, options).then(res => res.json());
}