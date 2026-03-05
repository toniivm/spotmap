<?php
/**
 * Crear spots de ejemplo para testing
 * Accede a: http://localhost/api/create-sample-spots.php
 */

header('Content-Type: application/json');

require_once '../src/bootstrap.php';

use SpotMap\Database;

try {
    $db = Database::pdo();
    
    $sampleSpots = [
        [
            'title' => '🏖️ Playa de las Catedrales',
            'description' => 'Hermosa playa con acantilados espectaculares. Perfecta para fotografía al atardecer.',
            'category' => 'playa',
            'tags' => 'playa, acantilados, fotografía, atardecer',
            'latitude' => 43.3823,
            'longitude' => -7.0492,
            'image_path' => 'defaults/beach.jpg',
            'rating' => 4.8,
            'status' => 'approved'
        ],
        [
            'title' => '🏔️ Pico del Buitre',
            'description' => 'Sendero de montaña con vistas panorámicas. Ideal para senderismo y escalada.',
            'category' => 'montaña',
            'tags' => 'montaña, senderismo, escalada, vistas',
            'latitude' => 40.3875,
            'longitude' => -3.6308,
            'image_path' => 'defaults/mountain.jpg',
            'rating' => 4.5,
            'status' => 'approved'
        ],
        [
            'title' => '🏰 Castillo Medieval',
            'description' => 'Castillo histórico del siglo XII. Increíble arquitectura medieval con torres y murallas intactas.',
            'category' => 'historia',
            'tags' => 'castillo, historia, arquitectura, medieval',
            'latitude' => 42.1729,
            'longitude' => -1.6438,
            'image_path' => 'defaults/castle.jpg',
            'rating' => 4.7,
            'status' => 'approved'
        ],
        [
            'title' => '🌳 Parque Natural',
            'description' => 'Bosque virgen con lágunas cristalinas. Perfecto para contemplar la naturaleza.',
            'category' => 'naturaleza',
            'tags' => 'naturaleza, bosque, lagos, verde',
            'latitude' => 42.8946,
            'longitude' => -6.3634,
            'image_path' => 'defaults/forest.jpg',
            'rating' => 4.6,
            'status' => 'approved'
        ],
        [
            'title' => '🍷 Bodega Histórica',
            'description' => 'Bodega tradicional con 200 años de historia. Tours de cata de vinos disponibles.',
            'category' => 'gastronomía',
            'tags' => 'vino, bodega, gastronomía, cata',
            'latitude' => 42.6343,
            'longitude' => -2.5184,
            'image_path' => 'defaults/wine.jpg',
            'rating' => 4.4,
            'status' => 'approved'
        ],
        [
            'title' => '🎨 Galería de Arte Contemporáneo',
            'description' => 'Galería de arte moderno con exposiciones rotativas. Ambiente creativo y acogedor.',
            'category' => 'arte',
            'tags' => 'arte, cultura, moderno, galerías',
            'latitude' => 40.4168,
            'longitude' => -3.7038,
            'image_path' => 'defaults/art.jpg',
            'rating' => 4.3,
            'status' => 'approved'
        ],
        [
            'title' => '⛪ Iglesia Barroca',
            'description' => 'Templo religioso del siglo XVII con decoraciones barrocas impresionantes.',
            'category' => 'religión',
            'tags' => 'iglesia, barroco, historia, arquitectura',
            'latitude' => 39.4699,
            'longitude' => -0.3763,
            'image_path' => 'defaults/church.jpg',
            'rating' => 4.5,
            'status' => 'approved'
        ],
        [
            'title' => '🍽️ Restaurante Michelin',
            'description' => 'Restaurante gourmet con estrella Michelin. Cocina fusión innovadora.',
            'category' => 'gastronomía',
            'tags' => 'restaurante, comida, michelin, gourmet',
            'latitude' => 41.3874,
            'longitude' => 2.1686,
            'image_path' => 'defaults/restaurant.jpg',
            'rating' => 4.9,
            'status' => 'approved'
        ],
        [
            'title' => '🏊 Piscina Natural',
            'description' => 'Piscina natural formada por agua de manantial. Agua fresca y cristalina.',
            'category' => 'playa',
            'tags' => 'agua, piscina, natural, baño',
            'latitude' => 38.8816,
            'longitude' => -1.6443,
            'image_path' => 'defaults/pool.jpg',
            'rating' => 4.7,
            'status' => 'approved'
        ],
        [
            'title' => '📚 Biblioteca Antigua',
            'description' => 'Biblioteca centenaria con miles de libros raros y manuscritos antiguos.',
            'category' => 'cultura',
            'tags' => 'biblioteca, libros, cultura, historia',
            'latitude' => 37.3889,
            'longitude' => -5.9844,
            'image_path' => 'defaults/library.jpg',
            'rating' => 4.6,
            'status' => 'approved'
        ]
    ];
    
    $created = 0;
    $errors = [];
    
    foreach ($sampleSpots as $spot) {
        try {
            $query = "
                INSERT INTO spots (
                    title, 
                    description, 
                    category, 
                    tags,
                    latitude, 
                    longitude, 
                    image_path, 
                    rating,
                    status,
                    user_id,
                    created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                $spot['title'],
                $spot['description'],
                $spot['category'],
                $spot['tags'],
                $spot['latitude'],
                $spot['longitude'],
                $spot['image_path'],
                $spot['rating'],
                $spot['status'],
                1  // user_id = 1 (admin)
            ]);
            
            $created++;
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
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
