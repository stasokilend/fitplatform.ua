// js/index.js - Логіка для головної сторінки

document.addEventListener('DOMContentLoaded', function() {
    const navLogin = document.getElementById('navLogin');
    const navRegister = document.getElementById('navRegister');
    const navDashboard = document.getElementById('navDashboard');
    const navLogout = document.getElementById('navLogout');
    const navAdmin = document.getElementById('navAdmin');
    const heroButtons = document.getElementById('heroButtons');
    const heroDashboard = document.getElementById('heroDashboard');

    // Перевіряємо авторизацію
    checkAuthSilent().then(profile => {
        if (profile) {
            // Авторизований – змінюємо інтерфейс
            if (navLogin) navLogin.style.display = 'none';
            if (navRegister) navRegister.style.display = 'none';
            if (navDashboard) navDashboard.style.display = 'block';
            if (navLogout) navLogout.style.display = 'block';
            if (heroButtons) heroButtons.style.display = 'none';
            if (heroDashboard) heroDashboard.style.display = 'block';

            // Адмін-панель
            if (navAdmin && ['admin', 'trainer'].includes(profile.role)) {
                navAdmin.style.display = 'block';
            }

            // Обробник виходу
            const logoutBtn = document.getElementById('logoutBtnIndex');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    logout('index.html');
                });
            }

            // Тост-привітання
            showToast('👋 Вітаємо, ' + (profile.full_name || 'користувач') + '!', 'success', 3000);

        } else {
            // Не авторизований – стандартний вигляд
            if (navLogin) navLogin.style.display = 'block';
            if (navRegister) navRegister.style.display = 'block';
            if (navDashboard) navDashboard.style.display = 'none';
            if (navLogout) navLogout.style.display = 'none';
            if (navAdmin) navAdmin.style.display = 'none';
            if (heroButtons) heroButtons.style.display = 'block';
            if (heroDashboard) heroDashboard.style.display = 'none';
        }
    });
});