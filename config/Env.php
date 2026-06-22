<?php

class Env
{
    private static array $values = [];
    private static bool $loaded = false;

    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;
        $path = $path ?: dirname(__DIR__) . '/.env';
        if (!is_readable($path)) {
            return;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if ($key === '') {
                continue;
            }
            self::$values[$key] = $value;
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? self::$values[$key] ?? $default;
        }
        if (is_string($value)) {
            $lower = strtolower($value);
            return match ($lower) {
                'true' => true,
                'false' => false,
                'null' => null,
                default => $value,
            };
        }
        return $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return filter_var(self::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }
}
