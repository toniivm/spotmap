<?php
namespace SpotMap\Controllers;

use SpotMap\DatabaseAdapter;
use SpotMap\ApiResponse;
use SpotMap\Validator;
use SpotMap\Cache;
use SpotMap\Auth;
use SpotMap\Logger;
use SpotMap\Constants;
use AuditLogger;

class SpotController
{
    /**
     * GET /spots - Listar todos los spots con paginación
     */
    public function index(): void
    {
        try {
            // Validación segura de parámetros
            $page = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
            $limit = min(100, max(1, filter_var($_GET['limit'] ?? 50, FILTER_VALIDATE_INT) ?: 50));
            $offset = ($page - 1) * $limit;
            // Advanced filters (category, tag, min_rating) placeholder parsing
            $category = isset($_GET['category']) ? trim($_GET['category']) : null;
            $tag = isset($_GET['tag']) ? trim($_GET['tag']) : null;
            $minRating = isset($_GET['min_rating']) ? filter_var($_GET['min_rating'], FILTER_VALIDATE_FLOAT) : null;

            // Cache primero
            $cacheKey = "spots_{$page}_{$limit}";
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                ApiResponse::success($cached);
                return;
            }

            $filters = [
                'category' => $category,
                'tag' => $tag,
                'schedule' => $_GET['schedule'] ?? null,
                'popularity' => isset($_GET['popularity']) ? (bool)$_GET['popularity'] : false,
                'center_lat' => $_GET['center_lat'] ?? null,
                'center_lng' => $_GET['center_lng'] ?? null,
                'max_distance_km' => $_GET['max_distance_km'] ?? null
            ];
            $result = DatabaseAdapter::getAllSpots($limit, $offset, $filters);
            
            if (isset($result['error'])) {
                ApiResponse::error($result['error'], 500);
                return;
            }

            $spots = $result['spots'] ?? [];
            $total = $result['total'] ?? count($spots);

            // Procesar tags JSON si es string
            array_walk($spots, function(&$spot) {
                if (isset($spot['tags']) && is_string($spot['tags'])) {
                    $spot['tags'] = json_decode($spot['tags']) ?: [];
                } else if (!isset($spot['tags'])) {
                    $spot['tags'] = [];
                }
            });

            // Field selection for list
            if (isset($_GET['fields'])) {
                $fields = array_filter(array_map('trim', explode(',', $_GET['fields'])));
                if ($fields) {
                    $spots = array_map(function($s) use ($fields) {
                        $filtered = [];
                        foreach ($fields as $f) if (array_key_exists($f, $s)) $filtered[$f] = $s[$f];
                        return $filtered;
                    }, $spots);
                }
            }

            $payload = [
                'spots' => $spots,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => (int)$total,
                    'pages' => ceil($total / $limit)
                ],
                'filters' => $filters
            ];
            // ETag y Last-Modified
            $lastModified = null;
            foreach ($spots as $s) {
                if (isset($s['created_at'])) {
                    $ts = strtotime($s['created_at']);
                    if ($ts && ($lastModified === null || $ts > $lastModified)) {
                        $lastModified = $ts;
                    }
                }
            }
            $etag = 'W/"spots_' . md5(json_encode(array_column($spots, 'id'))) . '"';
            // Condicional If-None-Match
            $ifNone = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
            if ($ifNone && $ifNone === $etag) {
                \SpotMap\ApiResponse::notModified($etag, $lastModified);
            }
            if (!headers_sent()) {
                if ($lastModified) header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
                header('ETag: ' . $etag);
            }
            // Cache persistente
            Cache::set($cacheKey, $payload, 60);
            ApiResponse::success($payload);

        } catch (\Exception $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }

    /**
     * POST /spots - Crear nuevo spot con validación robusta
     * Soporta tanto JSON como FormData (para upload de fotos)
     */
    public function store(): void
    {
        try {
            // Auth requerido para crear
            $user = Auth::requireUser();
            $token = Auth::getBearerToken();
            // Obtener datos: JSON o FormData
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            $input = null;
        
            if (strpos($contentType, 'multipart/form-data') !== false) {
                $input = $_POST;
            } else {
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            }

            if (!is_array($input)) {
                ApiResponse::error('Invalid request data', 400);
                return;
            }

            $hasUpload = (isset($_FILES['image1']) && $_FILES['image1']['error'] === UPLOAD_ERR_OK)
                || (isset($_FILES['image2']) && $_FILES['image2']['error'] === UPLOAD_ERR_OK);
            $hasImagePath = !empty($input['image_path']) || !empty($input['image_path_2']);
            if (!$hasUpload && !$hasImagePath) {
                ApiResponse::validation(['images' => ['Se requiere al menos una imagen']]);
                return;
            }
        
            // Parsear JSON fields si es necesario
            if (isset($input['tags']) && is_string($input['tags'])) {
                $tags = json_decode($input['tags'], true);
                $input['tags'] = is_array($tags) ? $tags : [];
            }

            // Validar
            $validator = new Validator();
            $validator->required($input['title'] ?? '', 'title');
            $validator->required($input['lat'] ?? '', 'lat');
            $validator->required($input['lng'] ?? '', 'lng');
            $validator->string($input['title'] ?? '', 'title', 1, 255);
            $validator->numeric($input['lat'] ?? '', 'lat');
            $validator->numeric($input['lng'] ?? '', 'lng');
            
            // Validación adicional: description (max 1000 caracteres)
            if (!empty($input['description'])) {
                $validator->string($input['description'], 'description', 0, Constants::SPOT_DESCRIPTION_MAX);
            }
            
            // Validación adicional: category (si existe, debe estar en lista permitida)
            if (!empty($input['category'])) {
                $validator->in($input['category'], 'category', Constants::SPOT_CATEGORIES);
            }
            
            // Validación adicional: tags (max 10 tags, cada uno max 50 caracteres)
            if (!empty($input['tags'])) {
                $validator->array($input['tags'], 'tags', Constants::TAG_MAX_COUNT, Constants::TAG_MAX_LENGTH);
            }

            if (isset($input['lat']) && is_numeric($input['lat'])) {
                $validator->latitude($input['lat'], 'lat');
            }
            if (isset($input['lng']) && is_numeric($input['lng'])) {
                $validator->longitude($input['lng'], 'lng');
            }

            if ($validator->fails()) {
                ApiResponse::validation($validator->errors());
                return;
            }

            // Preparar datos
            $spotData = [
                'title' => trim($input['title']),
                'description' => trim($input['description'] ?? ''),
                'lat' => (float)$input['lat'],
                'lng' => (float)$input['lng'],
                'category' => isset($input['category']) ? trim($input['category']) : null,
                'tags' => isset($input['tags']) && is_array($input['tags']) ? $input['tags'] : []
            ];
            
            // Ownership: set whenever user id is available
            if (isset($user['id'])) {
                $spotData['user_id'] = $user['id'];
            }
            
            // Status assignment: usuarios normales → pending, moderadores → approved
            $userRole = $user['role'] ?? Constants::DEFAULT_ROLE;
            $spotData['status'] = Constants::isModerator($userRole) ? 'approved' : 'pending';

            // Manejar hasta 2 fotos si están presentes
            $uploadedImages = [];
            $imagePaths = ['image1' => 'image_path', 'image2' => 'image_path_2'];
            
            foreach ($imagePaths as $fileKey => $dbColumn) {
                if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                    $photo = $_FILES[$fileKey];
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    $validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    
                    if ($photo['size'] > $maxSize) {
                        ApiResponse::validation([$fileKey => ['Image cannot exceed 5MB']]);
                        return;
                    }
                    if (!in_array($photo['type'], $validTypes)) {
                        ApiResponse::validation([$fileKey => ['Invalid image format']]);
                        return;
                    }
                    
                    $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('spot_') . '.' . $ext;

                    if (\SpotMap\DatabaseAdapter::useSupabase()) {
                        // Subida a Supabase Storage
                        try {
                            $bucket = 'spots';
                            $path = $bucket . '/' . $filename;
                            $fileContents = file_get_contents($photo['tmp_name']);
                            $uploadOk = \SpotMap\SupabaseStorage::upload($path, $fileContents, $photo['type']);
                            if ($uploadOk) {
                                $publicUrl = \SpotMap\SupabaseStorage::publicUrl($path);
                                $spotData[$dbColumn] = $publicUrl;
                                $uploadedImages[] = $publicUrl;
                            }
                        } catch (\Throwable $e) {
                            ApiResponse::error('Storage upload failed: ' . $e->getMessage(), 500);
                            return;
                        }
                    } else {
                        // Guardar en disco local
                        $uploadDir = __DIR__ . '/../../public/uploads/spots/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $uploadPath = $uploadDir . $filename;
                        if (move_uploaded_file($photo['tmp_name'], $uploadPath)) {
                            $relativePath = '/uploads/spots/' . $filename;
                            $spotData[$dbColumn] = $relativePath;
                            $uploadedImages[] = $relativePath;
                        }
                    }
                }
            }

            // Crear spot usando DatabaseAdapter
            $result = DatabaseAdapter::createSpot($spotData, $token);

            if (isset($result['error'])) {
                ApiResponse::error($result['error'], 500);
                return;
            }

            Cache::flushPattern('spots_*');
            ApiResponse::success($result, 'Spot created successfully', 201);

        } catch (\Exception $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }

    /**
     * PUT /spots/{id} - Actualizar datos (solo propietario o moderador/admin)
     */
    public function update(int $id): void
    {
        try {
            $user = Auth::requireUser();
            $token = Auth::getBearerToken();
            
            if ($id <= 0) {
                ApiResponse::error('Invalid ID', 400);
                return;
            }

            // Verificar que el spot existe
            $existing = DatabaseAdapter::getSpotById($id);
            if (isset($existing['error'])) {
                ApiResponse::notFound('Spot not found');
                return;
            }

            // Verificar autoridad: propietario o admin/moderador
            $userRole = $user['role'] ?? 'user';
            $isOwner = (isset($existing['user_id']) && $existing['user_id'] === $user['id']);
            $isModeratorOrAdmin = in_array($userRole, ['moderator', 'admin']);
            
            if (!$isOwner && !$isModeratorOrAdmin) {
                ApiResponse::unauthorized('Only owner or moderator can update spot');
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            if (empty($input)) {
                ApiResponse::error('No fields to update', 400);
                return;
            }

            // Validar campos actualizables
            $validator = new Validator();
            $fields = [];
            
            // Validar title si existe
            if (isset($input['title'])) {
                $validator->string($input['title'], 'title', 1, 255);
                $fields['title'] = trim($input['title']);
            }
            
            // Validar description si existe
            if (isset($input['description'])) {
                $validator->string($input['description'], 'description', 0, 1000);
                $fields['description'] = trim($input['description']);
            }
            
            // Validar category si existe
            if (isset($input['category'])) {
                $validator->in($input['category'], 'category', Constants::SPOT_CATEGORIES);
                $fields['category'] = trim($input['category']);
            }
            
            // Validar tags si existe
            if (isset($input['tags'])) {
                $validator->array($input['tags'], 'tags', Constants::TAG_MAX_COUNT, Constants::TAG_MAX_LENGTH);
                $fields['tags'] = is_array($input['tags']) ? $input['tags'] : [];
            }
            
            // Solo moderators/admin pueden cambiar status
            if (isset($input['status']) && $isModeratorOrAdmin) {
                $validator->in($input['status'], 'status', Constants::SPOT_STATUS);
                $fields['status'] = $input['status'];
            }

            if ($validator->fails()) {
                ApiResponse::validation($validator->errors());
                return;
            }

            if (empty($fields)) {
                ApiResponse::error('No valid fields to update', 400);
                return;
            }

            // Actualizar en BD
            $updated = DatabaseAdapter::updateSpot($id, $fields, $token);
            
            if (isset($updated['error'])) {
                ApiResponse::error($updated['error'], 500);
                return;
            }

            // Limpiar caché
            Cache::delete("spot_{$id}");
            Cache::flushPattern('spots_*');
            
            ApiResponse::success($updated, 'Spot updated successfully');

        } catch (\Exception $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }

    /**
     * GET /spots/{id} - Obtener un spot específico
     */
    public function show(int $id): void
    {
        try {
            if ($id <= 0) {
                ApiResponse::error('Invalid ID', 400);
                return;
            }

            $cacheKey = "spot_{$id}";
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                ApiResponse::success($cached);
                return;
            }

            $result = DatabaseAdapter::getSpotById($id);

            if (isset($result['error'])) {
                ApiResponse::notFound('Spot not found');
                return;
            }

            // Procesar tags
            if (isset($result['tags']) && is_string($result['tags'])) {
                $result['tags'] = json_decode($result['tags']) ?: [];
            }
            // Field selection for single spot
            if (isset($_GET['fields'])) {
                $fields = array_filter(array_map('trim', explode(',', $_GET['fields'])));
                if ($fields) {
                    $filtered = [];
                    foreach ($fields as $f) if (array_key_exists($f, $result)) $filtered[$f] = $result[$f];
                    $result = $filtered;
                }
            }
            
            // ETag y Last-Modified para detalle
            $etag = 'W/"spot_' . $id . '_' . md5(json_encode($result)) . '"';
            if (isset($result['created_at']) && !headers_sent()) {
                $ts = strtotime($result['created_at']);
                $ifNone = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
                if ($ifNone && $ifNone === $etag) {
                    \SpotMap\ApiResponse::notModified($etag, $ts);
                }
                if ($ts) header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $ts) . ' GMT');
                header('ETag: ' . $etag);
            }
            Cache::set($cacheKey, $result, 120);
            ApiResponse::success($result);

        } catch (\Exception $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }

    /**
     * DELETE /spots/{id} - Eliminar un spot y su foto asociada
     */
    public function destroy(int $id): void
    {
        try {
            // Auth requerido para eliminar
            $user = Auth::requireUser();
            $token = Auth::getBearerToken();
            if ($id <= 0) {
                ApiResponse::error('Invalid ID', 400);
                return;
            }

            // Obtener spot para eliminar foto
            $spot = DatabaseAdapter::getSpotById($id);

            if (isset($spot['error'])) {
                ApiResponse::notFound('Spot not found');
                return;
            }

            // Admin only
            $role = \SpotMap\Roles::getUserRole($user);
            if ($role !== 'admin') {
                ApiResponse::unauthorized('Admin only');
            }

            // Eliminar imagen previa (Supabase Storage o local)
            if (isset($spot['image_path']) && $spot['image_path']) {
                $old = $spot['image_path'];
                if (\SpotMap\DatabaseAdapter::useSupabase()) {
                    try {
                        \SpotMap\SupabaseStorage::deleteIfBucketPath($old);
                    } catch (\Throwable $e) { /* ignorar */ }
                } else {
                    $filePath = __DIR__ . '/../../public' . $old;
                    if (file_exists($filePath)) { @unlink($filePath); }
                }
            }

            // Eliminar de la base de datos
            $result = DatabaseAdapter::deleteSpot($id, $token);

            if (isset($result['error'])) {
                ApiResponse::error($result['error'], 500);
                return;
            }

            Cache::delete("spot_{$id}");
            Cache::flushPattern('spots_*');
            ApiResponse::success(null, 'Spot deleted successfully', 204);

        } catch (\Exception $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }

    /**
     * POST /spots/{id}/photo - Subir foto a un spot con validación robusta
     */
    public function uploadPhoto(int $id): void
    {
        try {
            // Auth requerido para subir foto
            $user = Auth::requireUser();
            $token = Auth::getBearerToken();
            if ($id <= 0) {
                ApiResponse::error('Invalid ID', 400);
                return;
            }

            // Verificar que el spot existe
            $spot = DatabaseAdapter::getSpotById($id);
            if (isset($spot['error'])) {
                ApiResponse::notFound('Spot not found');
                return;
            }

            // Ownership check
            if (\SpotMap\Config::get('OWNERSHIP_ENABLED') && isset($spot['user_id']) && isset($user['id']) && $spot['user_id'] !== $user['id']) {
                ApiResponse::unauthorized('Not owner of spot');
            }

            // Validar que hay archivo
            if (!isset($_FILES['photo'])) {
                ApiResponse::error('No file uploaded', 400);
                return;
            }

            $file = $_FILES['photo'];

            // Validar
            $validator = new Validator();
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $validator->mimeType($file, 'photo', $allowedMimes);
            $validator->fileSize($file, 'photo', 5 * 1024 * 1024);

            if ($validator->fails()) {
                ApiResponse::validation($validator->errors());
                return;
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                ApiResponse::error('Upload error: ' . $file['error'], 400);
                return;
            }

            // Guardar archivo (Supabase Storage o local)
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'spot_' . $id . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../../public/uploads/spots/';

            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    ApiResponse::error('Failed to create upload directory', 500);
                    return;
                }
            }

            $filepath = $uploadDir . $filename;
            $relativePath = '/uploads/spots/' . $filename;

            if (\SpotMap\DatabaseAdapter::useSupabase()) {
                try {
                    $bucket = 'spots';
                    $path = $bucket . '/' . $filename;
                    $fileContents = file_get_contents($file['tmp_name']);
                    $uploadOk = \SpotMap\SupabaseStorage::upload($path, $fileContents, $file['type']);
                    if (!$uploadOk) {
                        ApiResponse::error('Failed to save file (Supabase)', 500);
                        return;
                    }
                    $relativePath = \SpotMap\SupabaseStorage::publicUrl($path);
                } catch (\Throwable $e) {
                    ApiResponse::error('Storage upload failed: ' . $e->getMessage(), 500);
                    return;
                }
            } else {
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    ApiResponse::error('Failed to save file', 500);
                    return;
                }
            }

            // Eliminar archivo anterior si existe
            if (isset($spot['image_path']) && $spot['image_path']) {
                if (\SpotMap\DatabaseAdapter::useSupabase()) {
                    \SpotMap\SupabaseStorage::deleteIfBucketPath($spot['image_path']);
                } else {
                    $oldPath = __DIR__ . '/../../public' . $spot['image_path'];
                    if (file_exists($oldPath)) { @unlink($oldPath); }
                }
            }

            // Actualizar base de datos
            $result = DatabaseAdapter::updateSpot($id, ['image_path' => $relativePath], $token);

            if (isset($result['error'])) {
                ApiResponse::error($result['error'], 500);
                return;
            }

            Cache::delete("spot_{$id}");
            Cache::flushPattern('spots_*');
            ApiResponse::success($result, 'Photo uploaded successfully', 200);

        } catch (\Exception $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }

    /**
     * POST /admin/spots/{id}/approve - Approve a pending spot (moderator+)
     */
    public function approveSpot(int $id): void
    {
        try {
            $user = Auth::requireUser();
            if (!$user) {
                ApiResponse::error('Unauthorized', 401);
                return;
            }

            if (!Constants::isModerator($user['role'] ?? 'user')) {
                ApiResponse::error('Forbidden: Moderator access required', 403);
                return;
            }

            // Get current spot state
            $spot = DatabaseAdapter::getSpotById($id);
            if (!$spot) {
                ApiResponse::notFound('Spot not found');
                return;
            }

            $oldStatus = $spot['status'] ?? 'unknown';
            
            // Update status to approved
            $result = DatabaseAdapter::updateSpot($id, ['status' => 'approved']);
            
            if (isset($result['error'])) {
                ApiResponse::error($result['error'], 500);
                return;
            }

            // Log moderation action
            $db = \SpotMap\Database::pdo();
            $auditLogger = new AuditLogger($db);
            $auditLogger->logModeration(
                $user['id'],
                'approve_spot',
                'spot',
                (string)$id,
                ['status' => $oldStatus],
                ['status' => 'approved'],
                $_POST['reason'] ?? 'Approved by moderator',
                [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]
            );

            Cache::delete("spot_{$id}");
            Cache::flushPattern('spots_*');
            
            ApiResponse::success([
                'spot_id' => $id,
                'new_status' => 'approved'
            ], 'Spot approved successfully');

        } catch (\Exception $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }

    /**
     * POST /admin/spots/{id}/reject - Reject a pending spot (moderator+)
     */
    public function rejectSpot(int $id): void
    {
        try {
            $user = Auth::requireUser();
            if (!$user) {
                ApiResponse::error('Unauthorized', 401);
                return;
            }

            if (!Constants::isModerator($user['role'] ?? 'user')) {
                ApiResponse::error('Forbidden: Moderator access required', 403);
                return;
            }

            // Get current spot state
            $spot = DatabaseAdapter::getSpotById($id);
            if (!$spot) {
                ApiResponse::notFound('Spot not found');
                return;
            }

            $oldStatus = $spot['status'] ?? 'unknown';
            
            // Get rejection reason from request body
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $reason = $input['reason'] ?? $_POST['reason'] ?? 'Rejected by moderator';
            
            // Update status to rejected
            $result = DatabaseAdapter::updateSpot($id, ['status' => 'rejected']);
            
            if (isset($result['error'])) {
                ApiResponse::error($result['error'], 500);
                return;
            }

            // Log moderation action
            $db = \SpotMap\Database::pdo();
            $auditLogger = new AuditLogger($db);
            $auditLogger->logModeration(
                $user['id'],
                'reject_spot',
                'spot',
                (string)$id,
                ['status' => $oldStatus],
                ['status' => 'rejected'],
                $reason,
                [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]
            );

            Cache::delete("spot_{$id}");
            Cache::flushPattern('spots_*');
            
            ApiResponse::success([
                'spot_id' => $id,
                'new_status' => 'rejected',
                'reason' => $reason
            ], 'Spot rejected successfully');

        } catch (\Exception $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }
}