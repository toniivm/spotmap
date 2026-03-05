<?php
namespace SpotMap;

/**
 * Cache simple basada en archivos temporales.
 * Pensada para respuestas de lectura frecuentes (spots index/show).
 */
class Cache
{
    private static string $prefix = 'spotmap_cache_';
    private static int $defaultTtl = 60; // segundos
    private static int $hits = 0;
    private static int $misses = 0;

    public static function get(string $key)
    {
        $file = self::filePath($key);
        if (!is_file($file)) return null;
        $data = json_decode(@file_get_contents($file), true);
        if (!$data) return null;
        if ($data['expires'] < time()) {
            @unlink($file);
            return null;
        }
        self::$hits++;
        return $data['value'];
    }

    public static function set(string $key, $value, ?int $ttl = null): void
    {
        self::$misses++;
        $file = self::filePath($key);
        $payload = [
            'value' => $value,
            'expires' => time() + ($ttl ?? self::$defaultTtl)
        ];
        @file_put_contents($file, json_encode($payload));
    }

    public static function delete(string $key): void
    {
        $file = self::filePath($key);
        if (is_file($file)) @unlink($file);
    }

    public static function flushPattern(string $pattern): void
    {
        $dir = sys_get_temp_dir();
        foreach (glob($dir . DIRECTORY_SEPARATOR . self::$prefix . $pattern) as $f) {
            @unlink($f);
        }
    }

    private static function filePath(string $key): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::$prefix . $safe . '.json';
    }

    public static function stats(): array
    {
        return [
            'hits' => self::$hits,
            'misses' => self::$misses,
        ];
    }
}
