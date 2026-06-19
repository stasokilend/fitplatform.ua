// Редактирование профиля
function initProfile() {
    const profileForm = document.getElementById('profileForm');
    if (!profileForm) return;
    
    // Live-валидация
    const inputs = profileForm.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            autoSaveProfile(this);
        });
    });
    
    // Обработка загрузки аватара
    const avatarInput = document.getElementById('avatarInput');
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            previewAvatar(this);
        });
    }
    
    // Обработка кнопки "Редактировать"
    const editBtn = document.getElementById('editProfileBtn');
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            toggleProfileEdit(true);
        });
    }
    
    // Обработка кнопки "Отмена"
    const cancelBtn = document.getElementById('cancelEditBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            toggleProfileEdit(false);
        });
    }
}

// Автосохранение профиля (AJAX)
function autoSaveProfile(input) {
    const form = input.closest('form');
    if (!form) return;
    
    const formData = new FormData(form);
    
    fetch('/api/profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Зміни збережено', 'success');
        }
    })
    .catch(error => console.error('Ошибка сохранения:', error));
}

// Предпросмотр аватара
function previewAvatar(input) {
    const preview = document.getElementById('avatarPreview');
    if (!preview || !input.files || !input.files[0]) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

// Переключение режима редактирования
function toggleProfileEdit(enable) {
    const inputs = document.querySelectorAll('.profile-input');
    const viewMode = document.querySelector('.profile-view-mode');
    const editMode = document.querySelector('.profile-edit-mode');
    
    if (enable) {
        inputs.forEach(input => input.disabled = false);
        if (viewMode) viewMode.style.display = 'none';
        if (editMode) editMode.style.display = 'block';
    } else {
        inputs.forEach(input => {
            input.disabled = true;
            // Восстановление данных из атрибутов
            if (input.dataset.original) {
                input.value = input.dataset.original;
            }
        });
        if (viewMode) viewMode.style.display = 'block';
        if (editMode) editMode.style.display = 'none';
    }
}

// Обновление данных профиля
function updateProfileData(data) {
    const fields = {
        'fullName': data.full_name,
        'email': data.email,
        'age': data.age,
        'weight': data.weight,
        'height': data.height,
        'gender': data.gender,
        'fitnessLevel': data.fitness_level,
        'goalType': data.goal_type,
        'targetWeight': data.target_weight,
        'medicalNotes': data.medical_notes
    };
    
    Object.keys(fields).forEach(key => {
        const element = document.getElementById(key);
        if (element) {
            element.value = fields[key];
            element.dataset.original = fields[key];
        }
    });
}