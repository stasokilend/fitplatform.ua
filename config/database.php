<?php
// config/database.php - налаштування для OpenServer

define('DB_HOST', '127.0.1.31'); // Адреса сервера бази даних
define('DB_NAME', 'fitplatform');
define('DB_USER', 'root');      // В OpenServer зазвичай root
define('DB_PASS', '');          // Пароль за замовчуванням порожній

// Налаштування для відображення помилок (на час розробки)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Часова зона
date_default_timezone_set('Europe/Kiev');
?>