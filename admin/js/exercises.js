// admin/js/exercises.js

document.addEventListener('DOMContentLoaded', function() {
    loadExercises();
    document.getElementById('saveExerciseBtn').addEventListener('click', addExercise);
});

async function loadExercises() {
    const check = await apiRequest('admin/check.php', 'GET');
    if (!check.success) {
        showToast('Доступ заборонено', 'error');
        setTimeout(() => window.location.href = '../dashboard.php', 1000);
        return;
    }

    // Використовуємо існуючий ендпоінт для отримання вправ
    const result = await apiRequest('../api/get-exercises.php?all=1', 'GET');
    if (!result.success) {
        showToast('Помилка завантаження вправ', 'error');
        return;
    }

    const exercises = result.exercises || [];
    const tbody = document.getElementById('exercisesBody');

    if (exercises.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Немає вправ</td></tr>';
        return;
    }

    tbody.innerHTML = exercises.map(e => `
        <tr>
            <td>${e.id}</td>
            <td>${e.name}</td>
            <td>${e.duration_minutes} хв</td>
            <td>${e.met_value}</td>
            <td>${['', 'Початківець', 'Середній', 'Просунутий'][e.difficulty] || e.difficulty}</td>
            <td>${e.equipment || '-'}</td>
            <td>
                <button class="btn btn-sm btn-danger delete-exercise-btn" data-id="${e.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

    document.querySelectorAll('.delete-exercise-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (confirm('Видалити вправу?')) {
                deleteExercise(id);
            }
        });
    });
}

async function addExercise() {
    const name = document.getElementById('addExName').value.trim();
    const duration = parseFloat(document.getElementById('addExDuration').value);
    const met = parseFloat(document.getElementById('addExMet').value);
    const difficulty = parseInt(document.getElementById('addExDifficulty').value);
    const description = document.getElementById('addExDescription').value.trim();
    const equipment = document.getElementById('addExEquipment').value.trim();
    const video_url = document.getElementById('addExVideo').value.trim();

    let muscle_groups = {};
    try {
        const val = document.getElementById('addExMuscles').value.trim();
        if (val) muscle_groups = JSON.parse(val);
    } catch (e) {}

    let contraindications = [];
    try {
        const val = document.getElementById('addExContraindications').value.trim();
        if (val) contraindications = JSON.parse(val);
    } catch (e) {}

    if (!name || !duration || !met) {
        showToast('Заповніть всі обов\'язкові поля', 'error');
        return;
    }

    const data = { name, duration_minutes: duration, met_value: met, difficulty, description, equipment, video_url, muscle_groups, contraindications };
    const result = await apiRequest('admin/add-exercise.php', 'POST', data);

    if (result.success) {
        showToast('Вправу додано', 'success');
        document.getElementById('addExerciseForm').reset();
        bootstrap.Modal.getInstance(document.getElementById('addExerciseModal')).hide();
        loadExercises();
    } else {
        showToast(result.error || 'Помилка', 'error');
    }
}

async function deleteExercise(id) {
    const result = await apiRequest('admin/delete-exercise.php', 'POST', { id });
    if (result.success) {
        showToast('Вправу видалено', 'success');
        loadExercises();
    } else {
        showToast(result.error || 'Помилка', 'error');
    }
}