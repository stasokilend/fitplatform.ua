// Система сповіщень
let notificationOffset = 0;
const notificationLimit = 10;
const shownNotificationIds = new Set(JSON.parse(localStorage.getItem('shownNotificationIds') || '[]'));
let notificationsInitialized = false;

document.addEventListener('DOMContentLoaded', initNotifications);

function initNotifications() {
    if (notificationsInitialized) return;
    if (!document.querySelector('.notification-badge') && !document.getElementById('notificationBell')) return;

    notificationsInitialized = true;
    bindNotificationActions();
    loadNotifications(true);
    setInterval(() => loadNotifications(true), 60000);
}

function bindNotificationActions() {
    document.getElementById('notificationBell')?.addEventListener('show.bs.dropdown', () => loadNotifications(true));
    document.getElementById('loadMoreBtn')?.addEventListener('click', () => loadNotifications(false));
    document.getElementById('markAllReadBtn')?.addEventListener('click', markAllNotificationsAsRead);
    document.getElementById('clearAllBtn')?.addEventListener('click', clearAllNotifications);
}

function loadNotifications(reset = true) {
    if (reset) notificationOffset = 0;

    fetch(`/api/notifications.php?action=get&limit=${notificationLimit}&offset=${notificationOffset}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) return;

            const payload = data.data || {};
            const count = Number(payload.unread_count ?? data.count ?? 0);
            const notifications = payload.notifications || [];

            updateNotificationBadge(count);
            renderNotificationList(notifications, reset);
            showNewNotificationPopups(notifications);

            notificationOffset += notifications.length;
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            if (loadMoreBtn) loadMoreBtn.style.display = notifications.length >= notificationLimit ? 'block' : 'none';
        })
        .catch(error => console.error('Помилка отримання сповіщень:', error));
}

function updateNotificationBadge(count) {
    document.querySelectorAll('.notification-badge').forEach(badge => {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
    });
}

function renderNotificationList(notifications, reset = true) {
    const list = document.getElementById('notificationList');
    const empty = document.getElementById('notificationEmpty');
    if (!list) return;

    if (reset) {
        list.querySelectorAll('.notification-item').forEach(item => item.remove());
    }

    const hasItems = notifications.length > 0 || list.querySelector('.notification-item');
    if (empty) empty.style.display = hasItems ? 'none' : 'block';

    notifications.forEach(notification => {
        if (list.querySelector(`[data-feed-id="${notification.feed_id}"]`)) return;
        list.insertAdjacentHTML('beforeend', notificationItemTemplate(notification));
    });
}

function notificationItemTemplate(notification) {
    const unread = Number(notification.is_read) === 0;
    const link = notification.link || '#';
    const safeTitle = escapeHtml(notification.title || 'Сповіщення');
    const safeMessage = escapeHtml(notification.message || '');
    const safeMeta = escapeHtml(notification.meta || timeAgo(notification.created_at));

    return `
        <div class="notification-item ${unread ? 'is-unread' : ''}" data-feed-id="${notification.feed_id}" data-id="${notification.id || ''}" data-kind="${notification.kind || 'notification'}">
            <a class="notification-card" href="${escapeAttribute(link)}" ${link === '#' ? 'role="button"' : ''} onclick="handleNotificationOpen(event, this)">
                <span class="notification-icon notification-icon-${notification.type || 'info'}">
                    <i class="bi ${escapeAttribute(notification.icon || 'bi-bell')}"></i>
                </span>
                <span class="notification-content">
                    <span class="notification-title">${safeTitle}</span>
                    <span class="notification-message">${safeMessage}</span>
                    <span class="notification-meta">${safeMeta}</span>
                </span>
                ${unread ? '<span class="notification-dot"></span>' : ''}
            </a>
        </div>
    `;
}

function showNewNotificationPopups(notifications) {
    notifications
        .filter(item => Number(item.is_read) === 0)
        .slice(0, 3)
        .forEach(item => {
            const key = item.feed_id || `${item.kind}-${item.id}`;
            if (shownNotificationIds.has(key)) return;
            shownNotificationIds.add(key);
            showNotificationPopup(item);
        });

    localStorage.setItem('shownNotificationIds', JSON.stringify(Array.from(shownNotificationIds).slice(-50)));
}

function showNotificationPopup(notification) {
    const container = ensureNotificationContainer();
    const existing = container.querySelector(`[data-feed-id="${notification.feed_id}"]`);
    if (existing) return;

    const html = `
        <div class="toast notification-toast notification-toast-${notification.type || 'info'}" data-feed-id="${notification.feed_id}" role="alert">
            <div class="notification-popup-body">
                <span class="notification-icon notification-icon-${notification.type || 'info'}"><i class="bi ${escapeAttribute(notification.icon || 'bi-bell-fill')}"></i></span>
                <span class="notification-content">
                    <strong>${escapeHtml(notification.title || 'FitPlatform')}</strong>
                    <span>${escapeHtml(notification.message || '')}</span>
                    <small>${escapeHtml(timeAgo(notification.created_at))}</small>
                </span>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    const toastElement = container.querySelector('.notification-toast:last-child');
    const toast = new bootstrap.Toast(toastElement, { delay: 7000 });
    toast.show();
    toastElement.addEventListener('hidden.bs.toast', function() { this.remove(); });
}

function handleNotificationOpen(event, element) {
    const item = element.closest('.notification-item');
    const id = item?.dataset.id;
    const kind = item?.dataset.kind;
    if (kind === 'notification' && id) markNotificationAsRead(id);
    if (element.getAttribute('href') === '#') event.preventDefault();
}

function markNotificationAsRead(id) {
    const body = new URLSearchParams({ action: 'read', id });
    return fetch('/api/notifications.php', { method: 'POST', body })
        .then(() => loadNotifications(true));
}

function markAllNotificationsAsRead() {
    return fetch('/api/notifications.php', { method: 'POST', body: new URLSearchParams({ action: 'read_all' }) })
        .then(() => loadNotifications(true));
}

function clearAllNotifications() {
    if (!confirm('Очистити всі сповіщення?')) return;
    return fetch('/api/notifications.php', { method: 'POST', body: new URLSearchParams({ action: 'delete_all' }) })
        .then(() => loadNotifications(true));
}

function showToast(message, type = 'info', title = 'FitPlatform') {
    showNotificationPopup({ feed_id: `toast-${Date.now()}`, type, title, message, icon: toastIcon(type), created_at: new Date().toISOString(), is_read: 1 });
}

function ensureNotificationContainer() {
    let container = document.getElementById('notificationContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificationContainer';
        container.className = 'position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    return container;
}

function toastIcon(type) {
    return { success: 'bi-check-circle-fill', warning: 'bi-exclamation-triangle-fill', danger: 'bi-x-circle-fill', info: 'bi-info-circle-fill', achievement: 'bi-trophy-fill', message: 'bi-chat-dots-fill', program: 'bi-file-earmark-plus-fill' }[type] || 'bi-info-circle-fill';
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function escapeAttribute(value) {
    return escapeHtml(value).replace(/"/g, '&quot;');
}

function timeAgo(timestamp) {
    const diff = Math.floor((Date.now() - new Date(timestamp).getTime()) / 1000);
    if (!timestamp || Number.isNaN(diff)) return 'щойно';
    if (diff < 60) return 'щойно';
    if (diff < 3600) return Math.floor(diff / 60) + ' хв тому';
    if (diff < 86400) return Math.floor(diff / 3600) + ' год тому';
    if (diff < 604800) return Math.floor(diff / 86400) + ' дн тому';
    return new Date(timestamp).toLocaleDateString('uk-UA');
}
