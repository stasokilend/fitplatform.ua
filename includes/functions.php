<?php
require_once __DIR__ . '/../config/Env.php';

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sanitize($input) {
    return e(strip_tags(trim((string) $input)));
}

function url(string $path = ''): string {
    Env::load();
    $base = rtrim((string) Env::get('APP_URL', ''), '/');
    $path = '/' . ltrim($path, '/');
    return ($base !== '' ? $base : '') . $path;
}

function asset(string $path): string {
    return url($path);
}

function getCurrentPage() { return basename($_SERVER['PHP_SELF']); }
function isActivePage($page) { return getCurrentPage() === $page ? 'active' : ''; }
function calculateBMR($weight, $height, $age, $gender) {
    if (!$weight || !$height || !$age) {
        return 0;
    }

    $bmr = 10 * (float)$weight + 6.25 * (float)$height - 5 * (int)$age;
    $bmr += ($gender === 'male') ? 5 : -161;

    return max(0, $bmr);
}

function getActivityMultiplier($fitnessLevel) {
    $multipliers = [
        'beginner' => 1.375,
        'intermediate' => 1.55,
        'advanced' => 1.725,
    ];

    return $multipliers[$fitnessLevel] ?? 1.375;
}

function getCalorieGoalAdjustment($goalType) {
    $adjustments = [
        'weight_loss' => -0.15,
        'muscle_gain' => 0.10,
        'endurance' => 0.05,
        'health' => 0,
    ];

    return $adjustments[$goalType] ?? 0;
}

function calculateCaloriePlan(array $profile): array {
    $weight = (float)($profile['weight'] ?? 0);
    $height = (float)($profile['height'] ?? 0);
    $age = (int)($profile['age'] ?? 0);
    $gender = $profile['gender'] ?? 'female';
    $goalType = $profile['goal_type'] ?? 'health';
    $fitnessLevel = $profile['fitness_level'] ?? 'beginner';

    $bmr = calculateBMR($weight, $height, $age, $gender);
    if ($bmr <= 0 || $weight <= 0) {
        return [
            'is_complete' => false,
            'bmr' => 0,
            'maintenance_calories' => 0,
            'target_calories' => 0,
            'calorie_delta' => 0,
            'protein_g' => 0,
            'fat_g' => 0,
            'carbs_g' => 0,
            'water_l' => 0,
        ];
    }

    $maintenance = $bmr * getActivityMultiplier($fitnessLevel);
    $target = $maintenance * (1 + getCalorieGoalAdjustment($goalType));

    $proteinPerKg = $goalType === 'muscle_gain' ? 2.0 : ($goalType === 'weight_loss' ? 1.8 : 1.6);
    $fatPerKg = 0.9;
    $proteinG = $weight * $proteinPerKg;
    $fatG = $weight * $fatPerKg;
    $carbCalories = max(0, $target - ($proteinG * 4) - ($fatG * 9));

    return [
        'is_complete' => true,
        'bmr' => (int)round($bmr),
        'maintenance_calories' => (int)round($maintenance),
        'target_calories' => (int)round($target),
        'calorie_delta' => (int)round($target - $maintenance),
        'protein_g' => (int)round($proteinG),
        'fat_g' => (int)round($fatG),
        'carbs_g' => (int)round($carbCalories / 4),
        'water_l' => round(max(1.5, $weight * 0.035), 1),
    ];
}
function getFitnessLevelLabel($level) { $labels = ['beginner'=>'Початківець','intermediate'=>'Середній','advanced'=>'Просунутий']; return $labels[$level] ?? $level; }
function getGoalTypeLabel($goal) { $labels = ['weight_loss'=>'Зниження ваги','muscle_gain'=>'Набір м\'язової маси','endurance'=>'Витривалість','health'=>'Здоров\'я']; return $labels[$goal] ?? $goal; }
function getGenderLabel($gender) { $labels = ['male'=>'Чоловік','female'=>'Жінка','other'=>'Інше']; return $labels[$gender] ?? $gender; }
function timeAgo($timestamp) { $diff = time() - strtotime($timestamp); if ($diff < 60) return 'Щойно'; if ($diff < 3600) return floor($diff / 60) . ' хв тому'; if ($diff < 86400) return floor($diff / 3600) . ' год тому'; if ($diff < 604800) return floor($diff / 86400) . ' дн тому'; return date('d.m.Y', strtotime($timestamp)); }
?>
