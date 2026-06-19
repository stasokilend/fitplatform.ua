// js/settings.js

document.addEventListener('DOMContentLoaded', function() {
    // Завантаження даних користувача
    loadUserData();

    // Форма особистих даних
    const accountForm = document.getElementById('accountForm');
    if (accountForm) {
        accountForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('accountError');
            errorDiv.classList.add('d-none');

            const full_name = document.getElementById('settingsName').value.trim();
            const email = document.getElementById('settingsEmail').value.trim();

            if (!full_name) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Введіть ім\'я';
                return;
            }
            if (!email) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Введіть email';
                return;
            }

            disableButton(btn, 'Збереження...');
            const result = await apiRequest('update-account.php', 'POST', { full_name, email });

            if (result.success) {
                showToast('Дані оновлено!', 'success');
                loadUserData();
            } else {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = result.error || 'Помилка';
                showToast(result.error || 'Помилка', 'error');
            }
            enableButton(btn);
        });
    }

    // Форма зміни пароля
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('passwordError');
            errorDiv.classList.add('d-none');

            const current = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;

            if (!current || !newPass || !confirm) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Заповніть всі поля';
                return;
            }
            if (newPass.length < 6) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Новий пароль має бути не менше 6 символів';
                return;
            }
            if (newPass !== confirm) {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = 'Паролі не співпадають';
                return;
            }

            disableButton(btn, 'Зміна...');
            const result = await apiRequest('change-password.php', 'POST', {
                current_password: current,
                new_password: newPass
            });

            if (result.success) {
                showToast('Пароль змінено!', 'success');
                document.getElementById('currentPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
            } else {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = result.error || 'Помилка';
                showToast(result.error || 'Помилка', 'error');
            }
            enableButton(btn);
        });
    }

    // Видалення аккаунта
    const deleteBtn = document.getElementById('deleteAccountBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (confirm('Ви впевнені, що хочете видалити аккаунт? Цю дію не можна скасувати!')) {
                if (confirm('ОСТАННЄ ПОПЕРЕДЖЕННЯ: Всі дані будуть втрачені. Видалити?')) {
                    deleteAccount();
                }
            }
        });
    }
});

async function loadUserData() {
    const result = await apiRequest('profile.php', 'GET');
    if (result.success && result.profile) {
        const p = result.profile;
        document.getElementById('settingsName').value = p.full_name || '';
        document.getElementById('settingsEmail').value = p.email || '';
    }
}

async function deleteAccount() {
    const result = await apiRequest('delete-account.php', 'POST');
    if (result.success) {
        showToast('Аккаунт видалено', 'info');
        setTimeout(() => window.location.href = 'register.html', 1500);
    } else {
        showToast(result.error || 'Помилка', 'error');
    }
}