<?php
/**
 * ⚠️ CÓDIGO PROPIETARIO - SPOTMAP ⚠️
 * Copyright (c) 2025 Antonio Valero
 * Todos los derechos reservados.
 * 
 * API REST protegida con sistema de seguridad avanzado
 * CONFIDENCIAL - Para uso interno únicamente
 */
declare(strict_types=1);

// Iniciar buffering para evitar que cualquier salida (BOM/avisos) rompa el JSON
ob_start();
$__req_start = microtime(true);

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Logger.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/SupabaseClient.php';
require __DIR__ . '/../src/DatabaseAdapter.php';
require __DIR__ . '/../src/ApiResponse.php';
require __DIR__ . '/../src/Validator.php';
require __DIR__ . '/../src/Security.php';
require __DIR__ . '/../src/PerformanceMonitor.php';
require __DIR__ . '/../src/ErrorTracker.php';
require __DIR__ . '/../src/Controllers/SpotController.php';
require __DIR__ . '/../src/Controllers/MonitoringController.php';
require __DIR__ . '/../src/Controllers/AuditController.php';
require __DIR__ . '/../src/Auth.php';

use SpotMap\Database;
use SpotMap\DatabaseAdapter;
use SpotMap\ApiResponse;
use SpotMap\Security;
use SpotMap\Controllers\SpotController;
use SpotMap\Controllers\MonitoringController;
use SpotMap\Controllers\AuditController;
use SpotMap\Logger;
use SpotMap\PerformanceMonitor;
use SpotMap\ErrorTracker;

// API Documentation endpoint
if (isset($_GET['docs']) || (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/docs')) {
    ob_clean();
    header('Location: api-docs.html');
    exit;
}

// OpenAPI spec endpoint
if (isset($_GET['openapi']) || (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/openapi.json')) {
    ob_clean();
    header('Content-Type: application/json');
    readfile(__DIR__ . '/../openapi.json');
    exit;
}

// Health check endpoint
if (isset($_GET['health']) || (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/health')) {
    ob_clean();
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'timestamp' => time(), 'service' => 'spotmap-api', 'version' => '1.2']);
    exit;
}

// 🔍 Monitoring Dashboard endpoint
if (isset($_GET['monitoring']) || (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/monitoring')) {
    ob_clean();
    header('Location: monitoring.html');
    exit;
}

// Inicializar configuración para acceso a variables
\SpotMap\Config::load();

// 🔍 INICIALIZAR MONITORING AVANZADO
PerformanceMonitor::start();
ErrorTracker::getInstance(); // Registra automáticamente handlers
$logger = Logger::getInstance();

// Log inicio de petición con detalles completos
$logger->info('Nueva petición API', [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'uri' => $_SERVER['REQUEST_URI'] ?? '',
    'path_info' => $_SERVER['PATH_INFO'] ?? '',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
]);

$__uri = $_SERVER['REQUEST_URI'] ?? '/api.php';
$__method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
register_shutdown_function(function() use ($__req_start, $__uri, $__method, $logger) {
    $elapsedSeconds = microtime(true) - $__req_start;
    try {
        $logger->logMetric(
            $GLOBALS['action'] ?? $__uri,
            $__method,
            http_response_code(),
            $elapsedSeconds,
            memory_get_peak_usage(true)
        );
    } catch (\Throwable $metricError) {
        // Evitar side effects de métricas en respuesta API.
    }
});

// ⚠️ SISTEMA DE SEGURIDAD AVANZADO ACTIVADO
\SpotMap\Security::setAdvancedSecurityHeaders();

// Seguridad: Headers CORS, CSP y otros
Security::setCORSHeaders(); // Usar * para desarrollo local
Security::setSecurityHeaders();

// Content-Type JSON
header("Content-Type: application/json");

// Verificar rate limiting
if (!\SpotMap\Security::checkRateLimit()) {
    ob_clean();
    $logger->warning('Rate limit exceeded', [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'
    ]);
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 429,
            'message' => 'Too many requests. Please slow down.'
        ]
    ]);
    exit;
}

// Manejo de preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inicializar DB local solo si no usamos Supabase
if (!DatabaseAdapter::useSupabase()) {
    Database::init();
}

$controller = new SpotController();
$monitoringController = new MonitoringController();
$auditController = new AuditController(Database::pdo());
$method = $_SERVER['REQUEST_METHOD'];

// Soporte para _method fallback (si algunos servidores bloquean DELETE)
if ($method === 'POST' && isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
}

// Soporte para X-HTTP-Method-Override header
if ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
}

// Obtener parametros de la URL
// Ejemplo: index.php?action=spots&id=1&sub=photo
$action = $_GET['action'] ?? 'spots';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$sub = $_GET['sub'] ?? null;

try {
    // 🔍 RUTAS DE AUDITORÍA (ADMIN-ONLY)
    if ($action === 'audit') {
        PerformanceMonitor::mark('audit_start');
        
        // Autenticar usuario (permitir fallo para manejarlo en controller)
        try {
            $user = \SpotMap\Auth::requireUser();
        } catch (\Exception $e) {
            ApiResponse::error('Unauthorized: Authentication required', 401);
            exit;
        }
        if (!$user) {
            ApiResponse::error('Unauthorized: Authentication required', 401);
            exit;
        }
        
        // GET /audit/logs - Get audit log entries
        if ($method === 'GET' && !$sub) {
            $auditController->getLogs($user);
            exit;
        }
        
        // GET /audit/stats - Get audit statistics
        if ($method === 'GET' && $sub === 'stats') {
            $auditController->getStats($user);
            exit;
        }
        
        // GET /audit/moderator/{id} - Get specific moderator's audit trail
        if ($method === 'GET' && $sub === 'moderator' && $id) {
            $auditController->getModeratorHistory($user, $id);
            exit;
        }
        
        // GET /audit/resource/{targetType}/{targetId} - Get resource audit trail
        if ($method === 'GET' && $sub === 'resource' && isset($_GET['target_type']) && isset($_GET['target_id'])) {
            $auditController->getResourceHistory($user, $_GET['target_type'], $_GET['target_id']);
            exit;
        }
        
        $logger->warning('Audit endpoint not found', ['sub' => $sub]);
        ApiResponse::notFound('Audit endpoint not found');
    }

    // 🔍 RUTAS DE MONITOREO
    if ($action === 'monitoring') {
        PerformanceMonitor::mark('monitoring_start');
        
        if ($method === 'GET' && $sub === 'logs') {
            $monitoringController->getLogs();
            exit;
        }
        if ($method === 'GET' && $sub === 'metrics') {
            $monitoringController->getMetrics();
            exit;
        }
        if ($method === 'GET' && $sub === 'alerts') {
            $monitoringController->getAlerts();
            exit;
        }
        if ($method === 'GET' && $sub === 'health') {
            $monitoringController->getHealth();
            exit;
        }
        
        $logger->warning('Monitoring endpoint not found', ['sub' => $sub]);
        ApiResponse::notFound('Monitoring endpoint not found');
    }

    // ⭐ GET /spots (listar todos)
    if ($method === 'GET' && $action === 'spots' && !$id) {
        PerformanceMonitor::mark('spots_list_start');
        $controller->index();
        PerformanceMonitor::mark('spots_list_end');
        $logger->info('GET /spots - Success', ['count' => count($_GET)]);
        exit;
    }

    // ⭐ POST /spots (crear)
    if ($method === 'POST' && $action === 'spots' && !$id) {
        PerformanceMonitor::mark('spots_create_start');
        $controller->store();
        PerformanceMonitor::mark('spots_create_end');
        exit;
    }

    // ⭐ GET /spots?id=1 (obtener uno)
    if ($method === 'GET' && $action === 'spots' && $id) {
        PerformanceMonitor::mark('spots_show_start');
        $controller->show($id);
        PerformanceMonitor::mark('spots_show_end');
        $logger->info("GET /spots/{$id} - Success");
        exit;
    }

    // ⭐ DELETE /spots?id=1 (eliminar)
    if ($method === 'DELETE' && $action === 'spots' && $id) {
        PerformanceMonitor::mark('spots_delete_start');
        $controller->destroy($id);
        PerformanceMonitor::mark('spots_delete_end');
        $logger->info("DELETE /spots/{$id} - Success");
        exit;
    }

    // ⭐ POST /spots?id=1&sub=photo (subir foto)
    if ($method === 'POST' && $action === 'spots' && $id && $sub === 'photo') {
        PerformanceMonitor::mark('photo_upload_start');
        $controller->uploadPhoto($id);
        PerformanceMonitor::mark('photo_upload_end');
        $logger->info("POST /spots/{$id}/photo - Success");
        exit;
    }

    // ⭐ POST /admin/spots?id=1&sub=approve (aprobar spot - moderador+)
    if ($method === 'POST' && $action === 'admin' && $sub === 'spots' && $id && isset($_GET['approve'])) {
        PerformanceMonitor::mark('spot_approve_start');
        $controller->approveSpot($id);
        PerformanceMonitor::mark('spot_approve_end');
        $logger->info("POST /admin/spots/{$id}/approve - Success");
        exit;
    }

    // ⭐ POST /admin/spots?id=1&sub=reject (rechazar spot - moderador+)
    if ($method === 'POST' && $action === 'admin' && $sub === 'spots' && $id && isset($_GET['reject'])) {
        PerformanceMonitor::mark('spot_reject_start');
        $controller->rejectSpot($id);
        PerformanceMonitor::mark('spot_reject_end');
        $logger->info("POST /admin/spots/{$id}/reject - Success");
        exit;
    }

    // ⭐ POST /api?action=deleteSpot&id=1 (eliminar spot - versión simple sin DELETE)
    if (($method === 'POST' || $method === 'GET') && $action === 'deleteSpot' && $id) {
        PerformanceMonitor::mark('spots_delete_start');
        $controller->destroy($id);
        PerformanceMonitor::mark('spots_delete_end');
        $logger->info("POST/GET /deleteSpot/{$id} - Success");
        exit;
    }

    // Si nada coincide
    $logger->warning('Route not found', [
        'action' => $action,
        'method' => $method,
        'id' => $id,
        'sub' => $sub
    ]);
    ApiResponse::notFound('Route not found');

} catch (Exception $e) {
    // 🚨 CAPTURAR Y LOGUEAR EXCEPCIONES
    $logger->critical('Unhandled exception in API', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'action' => $action ?? 'unknown',
        'method' => $method ?? 'unknown'
    ]);
    
    // Log del backtrace
    $logger->debug('Exception backtrace', ['trace' => $e->getTraceAsString()]);
    
    // Registrar tiempo total
    $summary = PerformanceMonitor::getSummary();
    $logger->info('Request completed with error', $summary);
    
    ApiResponse::serverError('Unexpected error', [
        'detail' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?>
