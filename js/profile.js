// js/profile.js

document.addEventListener('DOMContentLoaded', function() {
    loadProfile();
});

async function loadProfile() {
    const result = await apiRequest('profile.php', 'GET');
    if (!result.success) {
        showToast('Помилка завантаження профілю', 'error');
        return;
    }

    const profile = result.profile;
    if (!profile) return;

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
}