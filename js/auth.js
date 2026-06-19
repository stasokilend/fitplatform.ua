// js/auth.js

document.addEventListener('DOMContentLoaded', function() {
    // === РЕЄСТРАЦІЯ ===
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const fullName = document.getElementById('fullName').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirmPassword').value;
            const errorDiv = document.getElementById('registerError');

            if (password !== confirm) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Паролі не співпадають';
                showToast('Паролі не співпадають', 'error');
                return;
            }
            if (password.length < 6) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Пароль має бути не менше 6 символів';
                showToast('Пароль має бути не менше 6 символів', 'error');
                return;
            }

            errorDiv.classList.add('d-none');
            const result = await apiRequest('register.php', 'POST', { email, password, full_name: fullName });

            if (result.success) {
                showToast('Реєстрація успішна! Тепер увійдіть у свій аккаунт.', 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);
            } else {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = result.error || 'Помилка реєстрації';
                showToast(result.error || 'Помилка реєстрації', 'error');
            }
        });
    }

    // === ВХІД ===
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('loginError');

            errorDiv.classList.add('d-none');
            const result = await apiRequest('login.php', 'POST', { email, password });

            if (result.success) {
                showToast('Вхід виконано!', 'success');
                // Перевіряємо, чи заповнений профіль
                const profileResult = await apiRequest('profile.php', 'GET');
                if (profileResult.success && profileResult.profile) {
                    const profile = profileResult.profile;
                    if (!profile.weight || !profile.age) {
                        // Профіль неповний – відправляємо на налаштування
                        setTimeout(() => window.location.href = 'profile-setup.html', 500);
                    } else {
                        setTimeout(() => window.location.href = 'dashboard.html', 500);
                    }
                } else {
                    // Профіль взагалі відсутній
                    setTimeout(() => window.location.href = 'profile-setup.html', 500);
                }
            } else {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = result.error || 'Невірний email або пароль';
                showToast(result.error || 'Невірний email або пароль', 'error');
            }
        });
    }

    // === КНОПКА ВИХОДУ ===
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }

    // === ЗАХИСТ СТОРІНОК ===
    const currentPath = window.location.pathname;
    const currentPage = currentPath.split('/').pop();
    const protectedPages = ['dashboard.html', 'stats.html', 'workout.html', 'profile-setup.html'];
    const isAdminPage = currentPath.includes('/admin/');

    if (protectedPages.includes(currentPage) || isAdminPage) {
        fetch(API_BASE + 'profile.php')
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    window.location.href = 'login.html';
                    return;
                }

                const profile = data.profile;

                // Сторінка профілю – завжди доступна (без редіректу)
                if (currentPage === 'profile-setup.html') {
                    return;
                }

                // Адмін-сторінки – перевіряємо роль
                if (isAdminPage) {
                    if (!profile || !['admin', 'trainer'].includes(profile.role)) {
                        showToast('Доступ заборонено. Потрібна роль адміністратора або тренера.', 'error');
                        setTimeout(() => window.location.href = 'dashboard.html', 1000);
                        return;
                    }
                    return; // Дозволено
                }

                // Інші захищені сторінки – перевіряємо заповненість профілю
                if (!profile || !profile.weight || !profile.age) {
                    window.location.href = 'profile-setup.html';
                }
                // Інакше залишаємось
            })
            .catch(() => {
                window.location.href = 'login.html';
            });
    }

    // === ПОКАЗ АДМІН-ПОСИЛАННЯ В НАВІГАЦІЇ ===
    const adminNavItem = document.getElementById('adminNavItem');
    if (adminNavItem) {
        fetch(API_BASE + 'profile.php')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.profile && ['admin', 'trainer'].includes(data.profile.role)) {
                    adminNavItem.style.display = 'block';
                } else {
                    adminNavItem.style.display = 'none';
                }
            })
            .catch(() => {});
    }
});