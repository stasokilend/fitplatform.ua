// js/profile-setup.js

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileSetupForm');
    const errorDiv = document.getElementById('profileSetupError');

    loadCurrentProfile();

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const age = parseInt(document.getElementById('setupAge').value);
        const weight = parseFloat(document.getElementById('setupWeight').value);
        const height = parseInt(document.getElementById('setupHeight').value);
        const gender = document.getElementById('setupGender').value;
        const level = parseInt(document.getElementById('setupLevel').value);

        let lose = parseFloat(document.getElementById('setupGoalLose').value) || 0;
        let muscle = parseFloat(document.getElementById('setupGoalMuscle').value) || 0;
        let endurance = parseFloat(document.getElementById('setupGoalEndurance').value) || 0;
        const sum = lose + muscle + endurance;
        if (sum === 0) {
            errorDiv.classList.remove('d-none');
            errorDiv.textContent = 'Сума пріоритетів не може дорівнювати 0.';
            showToast('Сума пріоритетів не може дорівнювати 0.', 'error');
            return;
        }
        // Нормалізація
        const goals = {
            lose_weight: lose / sum,
            gain_muscle: muscle / sum,
            endurance: endurance / sum
        };

        const restrictions = [];
        document.querySelectorAll('#profileSetupForm input[type="checkbox"]:checked').forEach(cb => {
            restrictions.push(cb.value);
        });

        const data = {
            age, weight, height, gender,
            fitness_level: level,
            goals: goals,
            medical_restrictions: restrictions
        };

        try {
            const result = await apiRequest('profile.php', 'POST', data);
            if (result.success) {
                showToast('Профіль успішно збережено!', 'success');
                setTimeout(() => window.location.href = 'dashboard.html', 1000);
            } else {
                errorDiv.classList.remove('d-none');
                errorDiv.textContent = result.error || 'Помилка збереження';
                showToast(result.error || 'Помилка збереження', 'error');
            }
        } catch (e) {
            errorDiv.classList.remove('d-none');
            errorDiv.textContent = 'Помилка з\'єднання з сервером';
            showToast('Помилка з\'єднання з сервером', 'error');
        }
    });

    async function loadCurrentProfile() {
        const result = await apiRequest('profile.php', 'GET');
        if (result.success && result.profile) {
            const p = result.profile;
            document.getElementById('setupAge').value = p.age || '';
            document.getElementById('setupWeight').value = p.weight || '';
            document.getElementById('setupHeight').value = p.height || '';
            document.getElementById('setupGender').value = p.gender || 'male';
            document.getElementById('setupLevel').value = p.fitness_level || 1;

            if (p.goals) {
                // Округлюємо до 2 знаків, щоб уникнути довгих дробів
                document.getElementById('setupGoalLose').value = parseFloat(p.goals.lose_weight || 0.33).toFixed(2);
                document.getElementById('setupGoalMuscle').value = parseFloat(p.goals.gain_muscle || 0.33).toFixed(2);
                document.getElementById('setupGoalEndurance').value = parseFloat(p.goals.endurance || 0.34).toFixed(2);
            }
            if (p.medical_restrictions) {
                const restrictions = p.medical_restrictions;
                document.querySelectorAll('#profileSetupForm input[type="checkbox"]').forEach(cb => {
                    cb.checked = restrictions.includes(cb.value);
                });
            }
        }
    }
});