<?php
namespace SpotMap;

use PDO;
use Exception;

class Database
{
    private static ?PDO $pdo = null;
    private static ?array $connectionInfo = null;

    public static function init(): void
    {
        if (self::$pdo) return;

        Config::load();

        $host = Config::get('DB_HOST');
        $port = Config::get('DB_PORT');
        $db   = Config::get('DB_DATABASE');
        $user = Config::get('DB_USERNAME');
        $pass = Config::get('DB_PASSWORD');
        $charset = 'utf8mb4';

        // Guardar info de conexión (sin contraseña por seguridad)
        self::$connectionInfo = [
            'host' => $host,
            'port' => $port,
            'database' => $db,
            'user' => $user,
            'charset' => $charset,

        ];

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 10, // 10 segundos
            ]);

            if (Config::isDebug()) {
                error_log("[DB] Conexión exitosa a {$host}:{$port}/{$db}");
            }
        } catch (\PDOException $e) {
            error_log("[DB ERROR] Fallo conexión: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos: " . ($e->getMessage() ?: 'Unknown error'), 0, $e);
        }
    }

    public static function pdo(): PDO
    {
        if (!self::$pdo) self::init();
        return self::$pdo;
    }

    public static function getConnectionInfo(): array
    {
        if (!self::$connectionInfo) self::init();
        return self::$connectionInfo ?? [];
    }

    public static function isConnected(): bool
    {
        try {
            if (!self::$pdo) return false;
            self::$pdo->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
