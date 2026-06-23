<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Env.php';

final class Cache
{
    private static Redis|Memcached|null $client = null;
    private static string $driver = 'file';

    public static function get(string $key, mixed $default = null): mixed
    {
        $payload = self::read($key);
        if ($payload === null) {
            return $default;
        }
        return $payload;
    }

    public static function set(string $key, mixed $value, int $ttl = 300): void
    {
        self::write($key, $value, $ttl);
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key, null);
        if ($cached !== null) {
            return $cached;
        }
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    private static function boot(): void
    {
        if (self::$client !== null || self::$driver !== 'file') {
            return;
        }
        Env::load();
        if (class_exists('Redis') && Env::get('REDIS_HOST')) {
            $redis = new Redis();
            if (@$redis->connect(Env::get('REDIS_HOST'), Env::int('REDIS_PORT', 6379), 1.0)) {
                self::$client = $redis;
                self::$driver = 'redis';
                return;
            }
        }
        if (class_exists('Memcached') && Env::get('MEMCACHED_HOST')) {
            $memcached = new Memcached();
            $memcached->addServer(Env::get('MEMCACHED_HOST'), Env::int('MEMCACHED_PORT', 11211));
            self::$client = $memcached;
            self::$driver = 'memcached';
        }
    }

    private static function read(string $key): mixed
    {
        self::boot();
        $cacheKey = 'fitplatform:' . $key;
        if (self::$driver === 'redis' && self::$client instanceof Redis) {
            $raw = self::$client->get($cacheKey);
            return $raw === false ? null : unserialize($raw);
        }
        if (self::$driver === 'memcached' && self::$client instanceof Memcached) {
            $value = self::$client->get($cacheKey);
            return self::$client->getResultCode() === Memcached::RES_SUCCESS ? $value : null;
        }
        $file = self::filePath($cacheKey);
        if (!is_file($file)) {
            return null;
        }
        $entry = unserialize((string) file_get_contents($file));
        if (!is_array($entry) || ($entry['expires_at'] ?? 0) < time()) {
            @unlink($file);
            return null;
        }
        return $entry['value'];
    }

    private static function write(string $key, mixed $value, int $ttl): void
    {
        self::boot();
        $cacheKey = 'fitplatform:' . $key;
        if (self::$driver === 'redis' && self::$client instanceof Redis) {
            self::$client->setex($cacheKey, $ttl, serialize($value));
            return;
        }
        if (self::$driver === 'memcached' && self::$client instanceof Memcached) {
            self::$client->set($cacheKey, $value, $ttl);
            return;
        }
        $file = self::filePath($cacheKey);
        file_put_contents($file, serialize(['expires_at' => time() + $ttl, 'value' => $value]), LOCK_EX);
    }

    private static function filePath(string $key): string
    {
        $dir = Env::get('CACHE_DIR', sys_get_temp_dir() . '/fitplatform-cache');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.cache';
    }
}
