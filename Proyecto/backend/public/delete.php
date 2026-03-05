<?php
/**
 * Endpoint simple para eliminar spots
 * Acceso: /backend/public/delete.php?id=24
 */
declare(strict_types=1);

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Logger.php';
require __DIR__ . '/../src/Auth.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/SupabaseClient.php';
require __DIR__ . '/../src/SupabaseStorage.php';
require __DIR__ . '/../src/DatabaseAdapter.php';
require __DIR__ . '/../src/ApiResponse.php';
require __DIR__ . '/../src/Roles.php';

use SpotMap\Database;
use SpotMap\DatabaseAdapter;
use SpotMap\Logger;
use SpotMap\Auth;
use SpotMap\ApiResponse;
use SpotMap\Roles;

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

\SpotMap\Config::load();
$logger = Logger::getInstance();

if (!DatabaseAdapter::useSupabase()) {
    Database::init();
}

try {
    // Autenticación requerida
    $user = Auth::requireUser();
    if (!$user) {
        ApiResponse::unauthorized('No autenticado');
    }
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : null);
    
    if (!$id || $id <= 0) {
        ApiResponse::error('ID inválido', 400);
    }
    
    // Log antes de eliminar
    $logger->info("API: Eliminando spot", ['id' => $id, 'user_id' => $user['id'] ?? 'unknown']);
    
    // Obtener el spot para verificar propiedad
    $spot = DatabaseAdapter::getSpotById($id);
    
    if (!$spot || isset($spot['error'])) {
        ApiResponse::notFound('Spot no encontrado');
    }
    
    // Solo admin
    $role = Roles::getUserRole($user);
    if ($role !== 'admin') {
        ApiResponse::error('No tienes permiso para eliminar este spot', 403);
    }
    
    // Eliminar imágenes de Supabase si existen
    if (DatabaseAdapter::useSupabase()) {
        if (isset($spot['image_path']) && $spot['image_path']) {
            try {
                \SpotMap\SupabaseStorage::deleteIfBucketPath($spot['image_path']);
            } catch (\Throwable $e) {
                $logger->warning("No se pudo eliminar imagen", ['path' => $spot['image_path'] ?? 'unknown']);
            }
        }
        if (isset($spot['image_path_2']) && $spot['image_path_2']) {
            try {
                \SpotMap\SupabaseStorage::deleteIfBucketPath($spot['image_path_2']);
            } catch (\Throwable $e) {
                $logger->warning("No se pudo eliminar imagen 2", ['path' => $spot['image_path_2'] ?? 'unknown']);
            }
        }
    }
    
    // Eliminar de la base de datos
    $result = DatabaseAdapter::deleteSpot($id);
    
    if (isset($result['error'])) {
        $logger->error("Error deleteting spot in DB", ['id' => $id, 'error' => $result['error']]);
        ApiResponse::serverError('Error eliminando spot', ['detail' => $result['error']]);
    }
    
    ApiResponse::success(['id' => $id], 'Spot eliminado correctamente', 200);
    $logger->info("Spot eliminado exitosamente", ['id' => $id, 'user_id' => $user['id'] ?? 'unknown']);
    
} catch (\Exception $e) {
    $logger->error("Error en delete.php", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    ApiResponse::serverError('Error en delete.php', ['detail' => $e->getMessage()]);
}
