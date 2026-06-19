// js/settings.js - Логіка сторінки налаштувань

document.addEventListener('DOMContentLoaded', function() {
    loadNav().then(() => {
        updateNavVisibility();
        loadUserData();
    });

    // Основні налаштування
    const settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('settingsError');

            const full_name = document.getElementById('settingsName').value.trim();
            const email = document.getElementById('settingsEmail').value.trim();

            if (!full_name) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Введіть ім\'я';
                showToast('Введіть ім\'я', 'error');
                return;
            }
            if (!email) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Введіть email';
                showToast('Введіть email', 'error');
                return;
            }

            errorDiv.classList.add('d-none');
            disableButton(btn, 'Збереження...');

            const result = await apiRequest('update-user.php', 'POST', { full_name, email });

            if (result.success) {
                showToast('Дані оновлено!', 'success');
                loadUserData();
                enableButton(btn);
            } else {
                enableButton(btn);
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = result.error || 'Помилка';
                showToast(result.error || 'Помилка', 'error');
            }
        });
    }

    // Зміна пароля
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('passwordError');

            const current_password = document.getElementById('currentPassword').value;
            const new_password = document.getElementById('newPassword').value;
            const confirm_password = document.getElementById('confirmPassword').value;

            if (new_password.length < 6) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Новий пароль має бути не менше 6 символів';
                showToast('Новий пароль має бути не менше 6 символів', 'error');
                return;
            }
            if (new_password !== confirm_password) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Паролі не співпадають';
                showToast('Паролі не співпадають', 'error');
                return;
            }

            errorDiv.classList.add('d-none');
            disableButton(btn, 'Зміна пароля...');

            const result = await apiRequest('change-password.php', 'POST', {
                current_password,
                new_password,
                confirm_password
            });

            if (result.success) {
                showToast('Пароль змінено!', 'success');
                passwordForm.reset();
                enableButton(btn);
            } else {
                enableButton(btn);
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = result.error || 'Помилка';
                showToast(result.error || 'Помилка', 'error');
            }
        });
    }

    // Видалення аккаунта
    const deleteBtn = document.getElementById('deleteAccountBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (confirm('Ви впевнені, що хочете видалити свій аккаунт? Це незворотна дія!')) {
                if (confirm('ВСІ ВАШІ ДАНІ БУДУТЬ ВТРАЧЕНІ. Продовжити?')) {
                    deleteAccount();
                }
            }
        });
    }
});

async function loadUserData() {
    const result = await apiRequest('profile.php', 'GET');
    if (!result.success || !result.profile) {
        showToast('Помилка завантаження даних', 'error');
        return;
    }

    const profile = result.profile;

    document.getElementById('settingsName').value = profile.full_name || '';
    document.getElementById('settingsEmail').value = profile.email || '';
    document.getElementById('userNameDisplay').textContent = profile.full_name || 'Користувач';
    document.getElementById('userEmailDisplay').textContent = profile.email || 'email@example.com';
    
    const avatar = document.getElementById('userAvatar');
    avatar.textContent = profile.full_name ? profile.full_name.charAt(0).toUpperCase() : 'U';

    const roleMap = { 'user': 'Користувач', 'trainer': 'Тренер', 'admin': 'Адміністратор' };
    document.getElementById('userRoleDisplay').textContent = roleMap[profile.role] || 'Користувач';
}

async function deleteAccount() {
    const result = await apiRequest('delete-account.php', 'POST');
    if (result.success) {
        showToast('Аккаунт видалено', 'success');
        setTimeout(() => window.location.href = 'index.html', 1500);
    } else {
        showToast(result.error || 'Помилка видалення', 'error');
    }
}