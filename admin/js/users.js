// admin/js/users.js

let editModal = null;

document.addEventListener('DOMContentLoaded', function() {
    editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    loadUsers();

    document.getElementById('saveUserBtn').addEventListener('click', saveUser);
});

async function loadUsers() {
    const check = await apiRequest('admin/check.php', 'GET');
    if (!check.success) {
        showToast('Доступ заборонено', 'error');
        setTimeout(() => window.location.href = '../dashboard.html', 1000);
        return;
    }

    const result = await apiRequest('admin/get-users.php', 'GET');
    if (!result.success) {
        showToast('Помилка завантаження користувачів', 'error');
        return;
    }

    const users = result.users;
    const tbody = document.getElementById('usersBody');

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">Немає користувачів</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(u => `
        <tr>
            <td>${u.id}</td>
            <td>${u.full_name}</td>
            <td>${u.email}</td>
            <td><span class="badge ${u.role === 'admin' ? 'bg-danger' : u.role === 'trainer' ? 'bg-warning' : 'bg-secondary'}">${u.role}</span></td>
            <td>${u.age || '-'}</td>
            <td>${u.weight || '-'}</td>
            <td>${u.height || '-'}</td>
            <td>${new Date(u.created_at).toLocaleDateString('uk-UA')}</td>
            <td>
                <button class="btn btn-sm btn-primary edit-user-btn" data-id="${u.id}" data-name="${u.full_name}" data-role="${u.role}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger delete-user-btn" data-id="${u.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

    // Події для кнопок
    document.querySelectorAll('.edit-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editUserId').value = this.dataset.id;
            document.getElementById('editUserName').value = this.dataset.name;
            document.getElementById('editUserRole').value = this.dataset.role;
            editModal.show();
        });
    });

    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.id;
            if (confirm('Ви впевнені, що хочете видалити цього користувача?')) {
                deleteUser(userId);
            }
        });
    });
}

async function saveUser() {
    const userId = document.getElementById('editUserId').value;
    const full_name = document.getElementById('editUserName').value.trim();
    const role = document.getElementById('editUserRole').value;

    if (!full_name) {
        showToast('Введіть ім\'я', 'error');
        return;
    }

    const result = await apiRequest('admin/update-user.php', 'POST', {
        user_id: userId,
        full_name: full_name,
        role: role
    });

    if (result.success) {
        showToast('Користувача оновлено', 'success');
        editModal.hide();
        loadUsers();
    } else {
        showToast(result.error || 'Помилка', 'error');
    }
}

async function deleteUser(userId) {
    const result = await apiRequest('admin/delete-user.php', 'POST', { user_id: userId });
    if (result.success) {
        showToast('Користувача видалено', 'success');
        loadUsers();
    } else {
        showToast(result.error || 'Помилка', 'error');
    }
}