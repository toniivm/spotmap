<?php
/**
 * Script para crear spots de ejemplo - Versión simplificada
 * Usa Supabase directamente
 */

// Configuración
require_once __DIR__ . '/../src/SupabaseClient.php';

$supabaseUrl = $_ENV['SUPABASE_URL'] ?? 'https://tqtfweuimowxpvmrfbau.supabase.co';
$supabaseKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? '';

if (!$supabaseKey) {
    die(json_encode(['error' => 'SUPABASE_SERVICE_KEY no configurada']));
}

// Spots de ejemplo
$sampleSpots = [
    [
        'title' => '🏖️ Playa de las Catedrales',
        'description' => 'Hermosa playa con acantilados espectaculares',
        'category' => 'playa',
        'tags' => 'playa, acantilados, fotografía',
        'latitude' => 43.3823,
        'longitude' => -7.0492,
        'rating' => 4.8
    ],
    [
        'title' => '🏔️ Pico del Buitre',
        'description' => 'Sendero de montaña con vistas panorámicas',
        'category' => 'montaña',
        'tags' => 'montaña, senderismo, escalada',
        'latitude' => 40.3875,
        'longitude' => -3.6308,
        'rating' => 4.5
    ],
    [
        'title' => '🏰 Castillo Medieval',
        'description' => 'Castillo histórico del siglo XII',
        'category' => 'historia',
        'tags' => 'castillo, historia, arquitectura',
        'latitude' => 42.1729,
        'longitude' => -1.6438,
        'rating' => 4.7
    ],
    [
        'title' => '🌳 Parque Natural',
        'description' => 'Bosque virgen con lágunas cristalinas',
        'category' => 'naturaleza',
        'tags' => 'naturaleza, bosque, lagos',
        'latitude' => 42.8946,
        'longitude' => -6.3634,
        'rating' => 4.6
    ],
    [
        'title' => '🍷 Bodega Histórica',
        'description' => 'Bodega tradicional con 200 años de historia',
        'category' => 'gastronomía',
        'tags' => 'vino, bodega, gastronomía',
        'latitude' => 42.6343,
        'longitude' => -2.5184,
        'rating' => 4.4
    ]
];

header('Content-Type: application/json; charset=utf-8');

try {
    $created = 0;
    $errors = [];
    
    foreach ($sampleSpots as $spot) {
        try {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $supabaseUrl . '/rest/v1/spots',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $supabaseKey,
                    'apikey: ' . $supabaseKey,
                    'Content-Type: application/json',
                    'Prefer: return=minimal'
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'title' => $spot['title'],
                    'description' => $spot['description'],
                    'category' => $spot['category'],
                    'tags' => $spot['tags'],
                    'latitude' => $spot['latitude'],
                    'longitude' => $spot['longitude'],
                    'rating' => $spot['rating'],
                    'status' => 'approved',
                    'user_id' => 'test-user-123'
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                $created++;
            } else {
                $errors[] = $spot['title'] . ' (HTTP ' . $httpCode . ')';
            }
        } catch (Exception $e) {
            $errors[] = $spot['title'] . ': ' . $e->getMessage();
        }
    }
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'created' => $created,
        'total' => count($sampleSpots),
        'errors' => $errors,
        'message' => "✅ Se crearon $created spots de ejemplo"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
