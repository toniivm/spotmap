<?php
namespace SpotMap;

use PDO;

class Migration
{
    private static PDO $pdo;

    public static function init(): void
    {
        self::$pdo = Database::pdo();
    }

    public static function up(): void
    {
        self::init();

        Logger::info("Iniciando migraciones...");

        // Crear tabla de migraciones si no existe
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Ejecutar migraciones pendientes
        $migrations = [
            '001_create_spots_table' => fn() => self::createSpotsTable(),
            '002_add_image_path_column' => fn() => self::addImagePathColumn(),
        ];

        foreach ($migrations as $name => $callback) {
            if (self::isMigrationExecuted($name)) {
                Logger::info("Migración ya ejecutada: $name");
                continue;
            }

            try {
                Logger::info("Ejecutando migración: $name");
                $callback();
                self::recordMigration($name);
                Logger::info("Migración exitosa: $name");
            } catch (\Exception $e) {
                Logger::error("Migración fallida: $name", ['error' => $e->getMessage()]);
                throw $e;
            }
        }

        Logger::info("Migraciones completadas.");
    }

    private static function createSpotsTable(): void
    {
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS spots (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                lat DOUBLE NOT NULL,
                lng DOUBLE NOT NULL,
                tags JSON NULL,
                category VARCHAR(100) NULL,
                image_path VARCHAR(500) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_lat (lat),
                INDEX idx_lng (lng),
                INDEX idx_category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private static function addImagePathColumn(): void
    {
        // Comprobar si la columna ya existe
        $stmt = self::$pdo->query("SHOW COLUMNS FROM spots LIKE 'image_path'");
        if ($stmt->rowCount() === 0) {
            Logger::info("Añadiendo columna image_path a tabla spots");
            self::$pdo->exec("
                ALTER TABLE spots 
                ADD COLUMN image_path VARCHAR(500) NULL AFTER category
            ");
        } else {
            Logger::info("Columna image_path ya existe");
        }
    }

    private static function isMigrationExecuted(string $name): bool
    {
        $stmt = self::$pdo->prepare("SELECT 1 FROM migrations WHERE migration = ?");
        $stmt->execute([$name]);
        return (bool)$stmt->fetchColumn();
    }

    private static function recordMigration(string $name): void
    {
        $stmt = self::$pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$name]);
    }

    public static function down(): void
    {
        self::init();

        Logger::warn("Ejecutando rollback...");

        self::$pdo->exec("DROP TABLE IF EXISTS spots");
        self::$pdo->exec("TRUNCATE TABLE migrations");

        Logger::warn("Rollback completado.");
    }
}
