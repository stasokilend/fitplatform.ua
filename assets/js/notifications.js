// Система уведомлений
function initNotifications() {
    // Проверка новых уведомлений
    if (document.querySelector('.notification-badge')) {
        checkNotifications();
        setInterval(checkNotifications, 60000); // Каждую минуту
    }
}

// Проверка уведомлений (AJAX)
function checkNotifications() {
    fetch('/api/notifications.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) return;

            const payload = data.data || {};
            const count = Number(data.count ?? payload.unread_count ?? 0);
            const notifications = data.notifications || payload.notifications || [];

            updateNotificationBadge(count);

            if (count > 0 && notifications.length > 0) {
                const newestUnread = notifications.find(item => Number(item.is_read) === 0) || notifications[0];
                showNotificationPopup(newestUnread);
            }
        })
        .catch(error => console.error('Ошибка получения уведомлений:', error));
}

// Обновление бейджа уведомлений
function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 9 ? '9+' : count;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Показать всплывающее уведомление
function showNotificationPopup(notification) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;
    
    // Проверка на дубликат
    const existing = container.querySelector(`[data-id="${notification.id}"]`);
    if (existing) return;
    
    const colors = {
        'success': 'bg-success',
        'warning': 'bg-warning',
        'danger': 'bg-danger',
        'info': 'bg-info',
        'achievement': 'bg-warning'
    };
    
    const bgColor = colors[notification.type] || 'bg-info';
    
    const html = `
        <div class="toast notification-toast" data-id="${notification.id}" role="alert">
            <div class="toast-header ${bgColor} text-white">
                <i class="bi ${notification.icon || 'bi-bell-fill'} me-2"></i>
                <strong class="me-auto">FitPlatform</strong>
                <small>${timeAgo(notification.created_at)}</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${notification.message}
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    
    const toastElement = container.querySelector('.notification-toast:last-child');
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    
    // Автоматическое удаление после скрытия
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Отметить уведомление как прочитанное
function markNotificationAsRead(id) {
    fetch('/api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: 'mark_read', id: id })
    });
}

// Показать уведомление
function showToast(message, type = 'info', title = 'FitPlatform') {
    const container = document.getElementById('notificationContainer');
    if (!container) {
        // Создаем контейнер если его нет
        const div = document.createElement('div');
        div.id = 'notificationContainer';
        div.className = 'position-fixed bottom-0 end-0 p-3';
        div.style.zIndex = '9999';
        document.body.appendChild(div);
    }
    
    const colors = {
        'success': 'bg-success',
        'warning': 'bg-warning',
        'danger': 'bg-danger',
        'info': 'bg-info'
    };
    
    const bgColor = colors[type] || 'bg-info';
    const icon = {
        'success': 'bi-check-circle-fill',
        'warning': 'bi-exclamation-triangle-fill',
        'danger': 'bi-x-circle-fill',
        'info': 'bi-info-circle-fill'
    }[type] || 'bi-info-circle-fill';
    
    const html = `
        <div class="toast notification-toast" role="alert">
            <div class="toast-header ${bgColor} text-white">
                <i class="bi ${icon} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    document.getElementById('notificationContainer').insertAdjacentHTML('beforeend', html);
    
    const toastElement = document.querySelector('.notification-toast:last-child');
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Вспомогательная функция timeAgo
function timeAgo(timestamp) {
    const diff = Math.floor((Date.now() - new Date(timestamp).getTime()) / 1000);
    
    if (diff < 60) return 'щойно';
    if (diff < 3600) return Math.floor(diff / 60) + ' хв тому';
    if (diff < 86400) return Math.floor(diff / 3600) + ' год тому';
    if (diff < 604800) return Math.floor(diff / 86400) + ' дн тому';
    return new Date(timestamp).toLocaleDateString('uk-UA');
}