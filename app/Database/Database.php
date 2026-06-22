<?php

namespace App\Database;

require_once __DIR__ . '/../../config/Env.php';

use Env;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        Env::load();
        $host = Env::get('DB_HOST', '127.0.1.31');
        $port = Env::get('DB_PORT', '3306');
        $name = Env::get('DB_NAME', 'fitness_platform');
        $user = Env::get('DB_USER', 'root');
        $pass = Env::get('DB_PASS', '');
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw $e;
        }

        return self::$connection;
    }
}
