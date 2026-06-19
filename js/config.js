// js/config.js

const API_BASE = '/api/';

// === TOAST СИСТЕМА ===
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toastContainer');
    if (!container) {
        // Якщо контейнера немає – створюємо його
        const newContainer = document.createElement('div');
        newContainer.id = 'toastContainer';
        newContainer.className = 'toast-container';
        document.body.prepend(newContainer);
        return showToast(message, type, duration);
    }

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
    setTimeout(() => toast.classList.add('show'), 10);

    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    });

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// === БЛОКУВАННЯ КНОПОК ===
function disableButton(btn, loadingText = 'Завантаження...') {
    btn.disabled = true;
    btn._originalText = btn.innerHTML;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${loadingText}`;
}

function enableButton(btn) {
    btn.disabled = false;
    if (btn._originalText) {
        btn.innerHTML = btn._originalText;
    }
}

// === ПЕРЕВІРКА АВТОРИЗАЦІЇ ===
function checkAuth() {
    return fetch(API_BASE + 'profile.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                window.location.href = '/login.php';
                return null;
            }
            return data.profile;
        })
        .catch(() => {
            window.location.href = '/login.php';
            return null;
        });
}

// === ПЕРЕВІРКА АВТОРИЗАЦІЇ (без редіректу) ===
function checkAuthSilent() {
    return fetch(API_BASE + 'profile.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return null;
            return data.profile;
        })
        .catch(() => null);
}

// === ВИХІД ===
function logout(redirectUrl = '/login.php') {
    fetch(API_BASE + 'logout.php', { method: 'POST' })
        .then(() => {
            showToast('Ви вийшли з аккаунту', 'info');
            setTimeout(() => window.location.href = redirectUrl, 500);
        })
        .catch(() => {
            window.location.href = redirectUrl;
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