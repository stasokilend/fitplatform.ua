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

function getCurrentPage() { return basename($_SERVER['PHP_SELF']); }
function isActivePage($page) { return getCurrentPage() === $page ? 'active' : ''; }
function calculateBMR($weight, $height, $age, $gender) { $bmr = 10 * $weight + 6.25 * $height - 5 * $age; $bmr += ($gender === 'male') ? 5 : -161; return $bmr; }
function getFitnessLevelLabel($level) { $labels = ['beginner'=>'Початківець','intermediate'=>'Середній','advanced'=>'Просунутий']; return $labels[$level] ?? $level; }
function getGoalTypeLabel($goal) { $labels = ['weight_loss'=>'Зниження ваги','muscle_gain'=>'Набір м\'язової маси','endurance'=>'Витривалість','health'=>'Здоров\'я']; return $labels[$goal] ?? $goal; }
function getGenderLabel($gender) { $labels = ['male'=>'Чоловік','female'=>'Жінка','other'=>'Інше']; return $labels[$gender] ?? $gender; }
function timeAgo($timestamp) { $diff = time() - strtotime($timestamp); if ($diff < 60) return 'Щойно'; if ($diff < 3600) return floor($diff / 60) . ' хв тому'; if ($diff < 86400) return floor($diff / 3600) . ' год тому'; if ($diff < 604800) return floor($diff / 86400) . ' дн тому'; return date('d.m.Y', strtotime($timestamp)); }
?>
