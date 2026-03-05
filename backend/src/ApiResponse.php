<?php
namespace SpotMap;

/**
 * Clase para respuestas estándar de la API
 * Compatible con REST estándar y fácil de migrar a Laravel
 */
class ApiResponse
{
    private static function basePayload(): array
    {
        $requestId = class_exists('\\SpotMap\\Logger') ? Logger::getRequestId() : null;
        $payload = [
            'version' => '1.0',
            'timestamp' => date('c'),
            'requestId' => $requestId,
        ];

        if (class_exists('\\SpotMap\\Config') && Config::isDebug()) {
            $payload['env'] = Config::get('ENV', 'development');
        }

        return $payload;
    }

    private static function setCommonHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        if (class_exists('\\SpotMap\\Logger')) {
            header('X-Request-ID: ' . Logger::getRequestId());
        }
    }

    public static function success($data = null, $message = 'Success', $statusCode = 200, $meta = null)
    {
        // Limpiar cualquier salida previa (BOM/avisos) para no romper el JSON
        if (ob_get_level() > 0) {
            ob_clean();
        }
        http_response_code($statusCode);
        $payload = array_merge(self::basePayload(), [
            'success' => true,
            'status' => $statusCode,
            'message' => $message,
            'data' => $data,
        ]);
        if ($meta !== null) {
            $payload['meta'] = $meta;
        }
        self::setCommonHeaders();
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!defined('SPOTMAP_TESTING') || SPOTMAP_TESTING !== true) {
            exit;
        }
    }

    public static function error($message = 'Error', $statusCode = 400, $errors = null, $meta = null)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        http_response_code($statusCode);
        $payload = array_merge(self::basePayload(), [
            'success' => false,
            'status' => $statusCode,
            'message' => $message,
            'errors' => $errors,
        ]);
        if ($meta !== null) {
            $payload['meta'] = $meta;
        }
        self::setCommonHeaders();
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!defined('SPOTMAP_TESTING') || SPOTMAP_TESTING !== true) {
            exit;
        }
    }

    public static function notFound($message = 'Resource not found')
    {
        self::error($message, 404);
    }

    public static function notModified(?string $etag = null, $lastModified = null)
    {
        if (ob_get_level() > 0) { ob_clean(); }
        http_response_code(304);
        if ($etag && !headers_sent()) header('ETag: ' . $etag);
        if ($lastModified && !headers_sent()) header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        exit;
    }

    public static function unauthorized($message = 'Unauthorized')
    {
        self::error($message, 401);
    }

    public static function validation($errors = [])
    {
        self::error('Validation failed', 422, $errors);
    }

    public static function serverError($message = 'Internal server error', $errors = null)
    {
        self::error($message, 500, $errors);
    }
}
