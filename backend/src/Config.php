<?php
namespace SpotMap;

/**
 * Gestiona la configuración centralizada del proyecto.
 * Lee desde backend/.env en desarrollo y variables de entorno en producción.
 */
class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) return;

        // Cargar .env si existe (desarrollo)
        // Orden: raíz del proyecto -> backend (backend tiene prioridad)
        $projectRoot = dirname(__DIR__, 2);
        self::loadEnvFile($projectRoot . '/.env');
        self::loadEnvFile($projectRoot . '/.env.local');
        self::loadEnvFile(__DIR__ . '/../.env');

        // Definir valores por defecto
        $defaults = [
            'ENV' => 'development',
            'DEBUG' => true,
            'STATUS_VERBOSE' => false,
            'ALLOW_INSECURE_JWT_FALLBACK' => false,
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'spotmap',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            'LOG_LEVEL' => 'INFO',
            'RATE_LIMIT_ENABLED' => false,
            'RATE_LIMIT_REQUESTS' => 100,
            'RATE_LIMIT_WINDOW' => 3600,
            'ROLE_CACHE_ENABLED' => true,
            'ROLE_CACHE_TTL' => 300,
            'IMAGE_CONVERT_TO_WEBP' => false,
            'IMAGE_WEBP_QUALITY' => 82,
            // Seguridad / CSP
            'CSP_DEFAULT' => "'self'",
            'CSP_SCRIPT' => "'self' https://cdn.jsdelivr.net https://unpkg.com",
            'CSP_STYLE' => "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com",
            'CSP_IMG' => "'self' data: https://unpkg.com https://raw.githubusercontent.com",
            'CSP_FONT' => "'self' data:",
            'CSP_CONNECT' => "'self'",
            'CSP_OBJECT' => "'none'",
            'CSP_BASE' => "'self'",
            'CSP_FRAME_ANCESTORS' => "'none'",
            // Ownership & Metrics
            'OWNERSHIP_ENABLED' => false,
            'METRICS_ENABLED' => false,
            'DIAGNOSTICS_ENABLED' => false,
            'ADMIN_EMAILS' => '',
        ];

        foreach ($defaults as $key => $defaultValue) {
            self::$config[$key] = getenv($key) ?: $defaultValue;
        }

        // Compatibilidad de alias históricos
        self::applyEnvAliases();

        self::$loaded = true;
    }

    private static function applyEnvAliases(): void
    {
        $aliases = [
            'DB_NAME' => 'DB_DATABASE',
            'DB_USER' => 'DB_USERNAME',
            'DB_PASS' => 'DB_PASSWORD',
            'APP_DEBUG' => 'DEBUG',
            'VITE_SUPABASE_URL' => 'SUPABASE_URL',
            'VITE_SUPABASE_ANON_KEY' => 'SUPABASE_ANON_KEY',
            'NEXT_PUBLIC_SUPABASE_URL' => 'SUPABASE_URL',
            'NEXT_PUBLIC_SUPABASE_ANON_KEY' => 'SUPABASE_ANON_KEY',
            'NEXT_PUBLIC_SUPABASE_PUBLISHABLE_DEFAULT_KEY' => 'SUPABASE_ANON_KEY',
        ];

        foreach ($aliases as $source => $target) {
            if (!array_key_exists($target, self::$config) || self::$config[$target] === '' || self::$config[$target] === null) {
                $value = getenv($source);
                if ($value !== false && $value !== '') {
                    self::$config[$target] = $value;
                    putenv($target . '=' . $value);
                    $_ENV[$target] = $value;
                }
            }
        }
    }

    private static function loadEnvFile(string $path): void
    {
        if (!file_exists($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = preg_replace('/^(["\'])?(.*)\\1$/', '$2', $value);

            putenv("$key=$value");
            $_ENV[$key] = $value;
                    self::$config[$key] = $value;
        }
    }

    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) self::load();
        return self::$config[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        if (!self::$loaded) self::load();
        return isset(self::$config[$key]);
    }

    public static function isDev(): bool
    {
        return self::get('ENV', 'development') === 'development';
    }

    public static function isProd(): bool
    {
        return self::get('ENV', 'development') === 'production';
    }

    public static function isDebug(): bool
    {
        return self::getBool('DEBUG', false);
    }

    public static function getAll(): array
    {
        if (!self::$loaded) self::load();
        // No devolver valores sensibles por seguridad
        $safe = self::$config;
        $safe['DB_PASSWORD'] = '***';
        if (isset($safe['SUPABASE_ANON_KEY']) && $safe['SUPABASE_ANON_KEY'] !== '') {
            $safe['SUPABASE_ANON_KEY'] = '***';
        }
        if (isset($safe['SUPABASE_SERVICE_KEY']) && $safe['SUPABASE_SERVICE_KEY'] !== '') {
            $safe['SUPABASE_SERVICE_KEY'] = '***';
        }
        return $safe;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);
        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $parsed ?? $default;
    }
}
