<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Env.php';

final class RateLimiter
{
    public static function hit(string $key, int $limit = 60, int $windowSeconds = 60): bool
    {
        Env::load();
        $dir = Env::get('RATE_LIMIT_DIR', sys_get_temp_dir() . '/fitplatform-rate-limit');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
        $now = time();
        $entry = ['started_at' => $now, 'hits' => 0];

        if (is_file($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded) && isset($decoded['started_at'], $decoded['hits'])) {
                $entry = $decoded;
            }
        }

        if (($now - (int) $entry['started_at']) >= $windowSeconds) {
            $entry = ['started_at' => $now, 'hits' => 0];
        }

        $entry['hits'] = (int) $entry['hits'] + 1;
        file_put_contents($file, json_encode($entry), LOCK_EX);

        return $entry['hits'] <= $limit;
    }
}
