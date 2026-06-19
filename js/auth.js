// js/auth.js

document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();

    // === ЯКЩО ВЖЕ АВТОРИЗОВАНИЙ – НЕ ПОКАЗУЄМО СТОРІНКИ ВХОДУ/РЕЄСТРАЦІЇ ===
    if (currentPage === 'login.html' || currentPage === 'register.html') {
        checkAuthSilent().then(profile => {
            if (profile) {
                // Вже авторизований – перенаправляємо на дашборд
                showToast('Ви вже авторизовані', 'info', 1500);
                setTimeout(() => window.location.href = 'dashboard.html', 1000);
                return;
            }
            // Інакше показуємо сторінку
        });
    }

    // === РЕЄСТРАЦІЯ ===
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            
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
            disableButton(btn, 'Реєстрація...');

            const result = await apiRequest('register.php', 'POST', { email, password, full_name: fullName });

            if (result.success) {
                showToast('Реєстрація успішна! Тепер увійдіть у свій аккаунт.', 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);
            } else {
                enableButton(btn);
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
            const btn = this.querySelector('button[type="submit"]');
            
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('loginError');

            errorDiv.classList.add('d-none');
            disableButton(btn, 'Вхід...');

            const result = await apiRequest('login.php', 'POST', { email, password });

            if (result.success) {
                showToast('Вхід виконано!', 'success');
                const profileResult = await apiRequest('profile.php', 'GET');
                if (profileResult.success && profileResult.profile) {
                    const profile = profileResult.profile;
                    if (!profile.weight || !profile.age) {
                        setTimeout(() => window.location.href = 'profile-setup.html', 500);
                    } else {
                        setTimeout(() => window.location.href = 'dashboard.html', 500);
                    }
                } else {
                    setTimeout(() => window.location.href = 'profile-setup.html', 500);
                }
            } else {
                enableButton(btn);
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
    const protectedPages = ['dashboard.html', 'stats.html', 'workout.html', 'profile-setup.html'];
    const isAdminPage = window.location.pathname.includes('/admin/');

    if (protectedPages.includes(currentPage) || isAdminPage) {
        checkAuthSilent().then(profile => {
            if (!profile) {
                window.location.href = 'login.html';
                return;
            }

            // Сторінка профілю – завжди доступна (без редіректу)
            if (currentPage === 'profile-setup.html') {
                return;
            }

            // Адмін-сторінки – перевіряємо роль
            if (isAdminPage) {
                if (!['admin', 'trainer'].includes(profile.role)) {
                    showToast('Доступ заборонено. Потрібна роль адміністратора або тренера.', 'error');
                    setTimeout(() => window.location.href = 'dashboard.html', 1000);
                    return;
                }
                return;
            }

            // Інші захищені сторінки – перевіряємо заповненість профілю
            if (!profile.weight || !profile.age) {
                window.location.href = 'profile-setup.html';
            }
        });
    }

    // === ПОКАЗ АДМІН-ПОСИЛАННЯ В НАВІГАЦІЇ ===
    const adminNavItem = document.getElementById('adminNavItem');
    if (adminNavItem) {
        checkAuthSilent().then(profile => {
            if (profile && ['admin', 'trainer'].includes(profile.role)) {
                adminNavItem.style.display = 'block';
            } else {
                adminNavItem.style.display = 'none';
            }
        });
    }

    // === АВТОМАТИЧНЕ ПРИХОВАННЯ ПОВІДОМЛЕНЬ ПРО ПОМИЛКИ ===
    document.querySelectorAll('.alert-danger').forEach(el => {
        if (el.textContent.trim()) {
            setTimeout(() => {
                el.classList.add('d-none');
            }, 5000);
        }
    });
});