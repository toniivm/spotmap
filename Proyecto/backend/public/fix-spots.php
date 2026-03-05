<?php
// fix-spots.php - Limpiar y reinsertar spots con coordenadas correctas

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Database.php';

use SpotMap\Database;

try {
    Database::init();
    $pdo = Database::pdo();
    
    // Eliminar spots con coordenadas 0,0 (los mal insertados)
    $stmt = $pdo->prepare("DELETE FROM spots WHERE (lat = 0 AND lng = 0) AND id > 1");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    
    // Reinsertar correctamente
    $spots = [
        ['Playa de la Malva', 'Una hermosa playa tranquila con vistas al atardecer', 40.4168, -3.7038, 'beach'],
        ['Mirador del Cerro', 'Punto con vista panorámica de la ciudad', 40.4200, -3.7000, 'viewpoint'],
        ['Restaurante Antojo', 'Comida tradicional con ingredientes frescos', 40.4150, -3.7050, 'food'],
        ['Parque Natural Bosque', 'Zona verde con senderos y fauna local', 40.4100, -3.6980, 'nature'],
        ['Mercado de Arte Callejero', 'Zona de arte urbano y galerías independientes', 40.4180, -3.7080, 'art']
    ];
    
    $inserted = 0;
    foreach ($spots as [$title, $desc, $lat, $lng, $cat]) {
        $stmt = $pdo->prepare("
            INSERT INTO spots (title, description, lat, lng, category, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $desc, $lat, $lng, $cat]);
        $inserted++;
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'deleted_bad_spots' => $deleted,
        'inserted_correct_spots' => $inserted,
        'message' => "✓ Limpieza y reinserción completada"
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
?>
