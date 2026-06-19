// js/profile.js

document.addEventListener('DOMContentLoaded', function() {
    // Завантаження профілю
    loadProfile();

    // Збереження профілю
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const age = parseInt(document.getElementById('editAge').value);
            const weight = parseFloat(document.getElementById('editWeight').value);
            const height = parseInt(document.getElementById('editHeight').value);
            const gender = document.getElementById('editGender').value;
            const level = parseInt(document.getElementById('editLevel').value);

            const lose = parseFloat(document.getElementById('goalLose').value) || 0;
            const muscle = parseFloat(document.getElementById('goalMuscle').value) || 0;
            const endurance = parseFloat(document.getElementById('goalEndurance').value) || 0;
            // Нормалізація цілей
            const sum = lose + muscle + endurance;
            const goals = {
                lose_weight: lose / sum,
                gain_muscle: muscle / sum,
                endurance: endurance / sum
            };

            // Збір обмежень
            const restrictions = [];
            document.querySelectorAll('#editProfileForm input[type="checkbox"]:checked').forEach(cb => {
                restrictions.push(cb.value);
            });

            const data = {
                age, weight, height, gender,
                fitness_level: level,
                goals: goals,
                medical_restrictions: restrictions
            };

            const result = await apiRequest('profile.php', 'POST', data);
            if (result.success) {
                alert('Профіль збережено! BMR: ' + result.bmr.toFixed(0) + ' ккал');
                loadProfile(); // Оновлення відображення
                // Закрити колапс
                const collapseEl = document.getElementById('editProfileForm');
                const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
                if (bsCollapse) bsCollapse.hide();
            } else {
                alert('Помилка: ' + (result.error || 'Невідома помилка'));
            }
        });
    }
});

async function loadProfile() {
    const result = await apiRequest('profile.php', 'GET');
    if (!result.success) {
        console.error('Помилка завантаження профілю');
        return;
    }

    const profile = result.profile;
    if (!profile) return;

    // Заповнення полів для відображення
    const fields = ['displayName', 'displayEmail', 'displayAge', 'displayWeight', 'displayHeight', 'displayGender', 'displayLevel', 'displayBmr'];
    const keys = ['full_name', 'email', 'age', 'weight', 'height', 'gender', 'fitness_level', 'bmr'];
    fields.forEach((elId, idx) => {
        const el = document.getElementById(elId);
        if (el) {
            let val = profile[keys[idx]] || '-';
            if (keys[idx] === 'gender') val = val === 'male' ? 'Чоловік' : 'Жінка';
            if (keys[idx] === 'fitness_level') {
                const levels = ['', 'Початківець', 'Середній', 'Просунутий'];
                val = levels[parseInt(val)] || val;
            }
            el.textContent = val;
        }
    });

    // Заповнення форми редагування (якщо є)
    if (document.getElementById('editAge')) {
        document.getElementById('editAge').value = profile.age || '';
        document.getElementById('editWeight').value = profile.weight || '';
        document.getElementById('editHeight').value = profile.height || '';
        document.getElementById('editGender').value = profile.gender || 'male';
        document.getElementById('editLevel').value = profile.fitness_level || 1;

        // Цілі
        const goals = profile.goals || { lose_weight: 0.33, gain_muscle: 0.33, endurance: 0.34 };
        document.getElementById('goalLose').value = goals.lose_weight || 0.33;
        document.getElementById('goalMuscle').value = goals.gain_muscle || 0.33;
        document.getElementById('goalEndurance').value = goals.endurance || 0.34;

        // Обмеження
        const restrictions = profile.medical_restrictions || [];
        document.querySelectorAll('#editProfileForm input[type="checkbox"]').forEach(cb => {
            cb.checked = restrictions.includes(cb.value);
        });
    }
}