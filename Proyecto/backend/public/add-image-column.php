<?php
// Script para agregar columna image_path_2 a la tabla spots
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/Config.php';

use SpotMap\Config;
use SpotMap\Database;

Config::load();
Database::init();

try {
    $pdo = Database::pdo();
    
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM spots LIKE 'image_path_2'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "Agregando columna image_path_2...\n";
        $pdo->exec("ALTER TABLE spots ADD COLUMN image_path_2 TEXT AFTER image_path");
        echo "✓ Columna image_path_2 agregada exitosamente\n";
    } else {
        echo "✓ La columna image_path_2 ya existe\n";
    }
    
    // Mostrar estructura de la tabla
    $stmt = $pdo->query("DESCRIBE spots");
    echo "\nEstructura de la tabla spots:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ Script completado exitosamente\n";
