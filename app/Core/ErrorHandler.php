<?php

namespace App\Core;

require_once __DIR__ . '/../../config/Env.php';

use Env;
use Throwable;

class ErrorHandler
{
    public static function register(): void
    {
        Env::load();
        $debug = Env::bool('APP_DEBUG', false);
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting(E_ALL);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleException(Throwable $exception): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        error_log('[' . date('c') . '] ' . $exception . PHP_EOL, 3, $logDir . '/app.log');

        if (Env::bool('APP_DEBUG', false)) {
            http_response_code(500);
            echo '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8') . '</pre>';
            return;
        }

        http_response_code(500);
        echo 'Вибачте, сталася помилка. Спробуйте пізніше.';
    }
}
