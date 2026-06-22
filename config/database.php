<?php
require_once __DIR__ . '/../app/Database/Database.php';

use App\Database\Database;

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/Env.php';
    Env::load();
    define('DB_HOST', Env::get('DB_HOST', 'localhost'));
    define('DB_PORT', Env::get('DB_PORT', '3306'));
    define('DB_NAME', Env::get('DB_NAME', 'fitness_platform'));
    define('DB_USER', Env::get('DB_USER', 'root'));
    define('DB_PASS', Env::get('DB_PASS', ''));
}

$pdo = Database::getConnection();
?>
