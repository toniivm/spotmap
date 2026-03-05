<?php
declare(strict_types=1);

// Iniciar buffering para evitar que cualquier salida (BOM/avisos) rompa el JSON
ob_start();
$__req_start = microtime(true);

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/Logger.php';
require __DIR__ . '/../src/RateLimiter.php';
require __DIR__ . '/../src/ApiResponse.php';
require __DIR__ . '/../src/Validator.php';
require __DIR__ . '/../src/SupabaseClient.php';
require __DIR__ . '/../src/DatabaseAdapter.php';
require __DIR__ . '/../src/Controllers/SpotController.php';
require __DIR__ . '/../src/Metrics.php';
require __DIR__ . '/../src/Cache.php';
require __DIR__ . '/../src/Auth.php';
require __DIR__ . '/../src/Roles.php';
require __DIR__ . '/../src/Security.php';
require __DIR__ . '/../src/Controllers/FavoritesController.php';
require __DIR__ . '/../src/Controllers/CommentsController.php';
require __DIR__ . '/../src/Controllers/RatingsController.php';
require __DIR__ . '/../src/Controllers/ReportsController.php';
require __DIR__ . '/../src/Controllers/AdminController.php';
require __DIR__ . '/../src/Controllers/NotificationController.php';
require __DIR__ . '/../src/Controllers/AccountController.php';

use SpotMap\Config;
use SpotMap\Database;
use SpotMap\DatabaseAdapter;
use SpotMap\Logger;
use SpotMap\RateLimiter;
use SpotMap\Controllers\SpotController;
use SpotMap\Metrics;
use SpotMap\Cache;
use SpotMap\Auth;
use SpotMap\Security;
use SpotMap\Controllers\FavoritesController;
use SpotMap\Controllers\CommentsController;
use SpotMap\Controllers\RatingsController;
use SpotMap\Controllers\ReportsController;
use SpotMap\Controllers\AdminController;
use SpotMap\Controllers\NotificationController;
use SpotMap\Controllers\AccountController;

// Inicializar configuración
Config::load();

if (!headers_sent()) {
    header('X-Request-ID: ' . Logger::getRequestId());
}

// Security headers (CSP, HSTS, etc)
Security::setSecurityHeaders();

// CORS mejorado
$allowedOrigins = array_map('trim', explode(',', Config::get('CORS_ORIGINS', 'http://localhost,http://localhost:3000')));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowAllOrigins = in_array('*', $allowedOrigins, true);
$isProd = Config::isProd();

// In production, wildcard origins are not accepted.
if ($isProd && $allowAllOrigins) {
    $allowAllOrigins = false;
    Logger::warn('CORS wildcard disabled in production');
}

$isAllowedOrigin = $allowAllOrigins || in_array($origin, $allowedOrigins, true);

if ($isAllowedOrigin) {
    if (!$isProd && $allowAllOrigins && $origin === '') {
        header('Access-Control-Allow-Origin: *');
    } else {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    }
    header('Vary: Origin');
}
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Request-ID, X-Status-Token, X-Diagnostic-Token");
header("Access-Control-Expose-Headers: Authorization, X-Request-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Inicializar base de datos local solo si NO usamos Supabase
if (!DatabaseAdapter::useSupabase()) {
    try {
        Database::init();
    } catch (\Exception $e) {
        Logger::error("DB initialization failed", ['error' => $e->getMessage()]);
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
}

// Obtener la ruta (sin index.php)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Rate limiting
$rateIdentifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!RateLimiter::check($rateIdentifier)) {
    http_response_code(429);
    $remaining = RateLimiter::getRemaining($rateIdentifier);
    if ($remaining >= 0) {
        header('X-RateLimit-Remaining: 0');
    }
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

$remaining = RateLimiter::getRemaining($rateIdentifier);
if ($remaining >= 0) {
    header('X-RateLimit-Remaining: ' . $remaining);
}

// Logging
Logger::info("Petición: $method $uri", ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
Metrics::inc('requests_total');

// Endpoint rápido para comprobar conexión a la base de datos
if ($uri === '/ping-db') {
    if (!Config::getBool('DIAGNOSTICS_ENABLED', false)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Route not found']);
        exit;
    }

    $diagnosticToken = trim((string)Config::get('DIAGNOSTIC_TOKEN', ''));
    if ($diagnosticToken !== '') {
        $provided = trim((string)($_SERVER['HTTP_X_DIAGNOSTIC_TOKEN'] ?? ''));
        if ($provided === '' || !hash_equals($diagnosticToken, $provided)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }

    try {
        $pdo = Database::pdo();
        $stmt = $pdo->query('SELECT 1');
        $ok = (bool)$stmt->fetchColumn();
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'db' => $ok, 'time' => date('c')]);
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($uri === '/db-info') {
    if (!Config::getBool('DIAGNOSTICS_ENABLED', false)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Route not found']);
        exit;
    }

    $diagnosticToken = trim((string)Config::get('DIAGNOSTIC_TOKEN', ''));
    if ($diagnosticToken !== '') {
        $provided = trim((string)($_SERVER['HTTP_X_DIAGNOSTIC_TOKEN'] ?? ''));
        if ($provided === '' || !hash_equals($diagnosticToken, $provided)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }

    try {
        $pdo = Database::pdo();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
        $result = [];
        foreach ($tables as $t) {
            $tableName = $t[0];
            $countStmt = $pdo->query("SELECT COUNT(*) AS c FROM `{$tableName}`");
            $count = (int)$countStmt->fetchColumn();
            $result[$tableName] = $count;
        }
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'tables' => $result]);
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Procesar la ruta: soportar llamadas donde index.php está en un subpath
$basePos = strpos($uri, '/spots');
if ($basePos !== false) {
    $route = substr($uri, $basePos);
} else {
    $route = $uri;
}

$parts = array_filter(explode('/', trim($route, '/')));
$parts = array_values($parts); // Re-indexar

// Simple router
if (isset($parts[0]) && $parts[0] === 'spots') {
    $controller = new SpotController();

    if ($method === 'GET' && count($parts) === 1) {
        Metrics::inc('requests_spots_list');
        $controller->index();
        exit;
    }

    if ($method === 'POST' && count($parts) === 1) {
        Metrics::inc('requests_spots_create');
        $controller->store();
        exit;
    }

    if ($method === 'GET' && count($parts) === 2) {
        Metrics::inc('requests_spots_show');
        $controller->show((int)$parts[1]);
        exit;
    }

    if ($method === 'PUT' && count($parts) === 2) {
        $controller->update((int)$parts[1]);
        exit;
    }

    if ($method === 'DELETE' && count($parts) === 2) {
        Metrics::inc('requests_spots_delete');
        $controller->destroy((int)$parts[1]);
        exit;
    }

    if ($method === 'POST' && count($parts) === 3 && $parts[2] === 'photo') {
        Metrics::inc('requests_spots_upload');
        $controller->uploadPhoto((int)$parts[1]);
        exit;
    }

    // Favorites
    if ($method === 'POST' && count($parts) === 3 && $parts[2] === 'favorite') {
        $fav = new FavoritesController();
        $fav->favorite((int)$parts[1]);
        exit;
    }
    if ($method === 'DELETE' && count($parts) === 3 && $parts[2] === 'favorite') {
        $fav = new FavoritesController();
        $fav->unfavorite((int)$parts[1]);
        exit;
    }
    if ($method === 'GET' && count($parts) === 3 && $parts[2] === 'favorites') {
        $fav = new FavoritesController();
        $fav->list((int)$parts[1]);
        exit;
    }

    // Comments
    if ($method === 'GET' && count($parts) === 3 && $parts[2] === 'comments') {
        $com = new CommentsController();
        $com->list((int)$parts[1]);
        exit;
    }
    if ($method === 'POST' && count($parts) === 3 && $parts[2] === 'comments') {
        $com = new CommentsController();
        $com->add((int)$parts[1]);
        exit;
    }
    if ($method === 'DELETE' && count($parts) === 4 && $parts[2] === 'comments') {
        $com = new CommentsController();
        $com->delete((int)$parts[3]);
        exit;
    }

    // Ratings
    if ($method === 'POST' && count($parts) === 3 && $parts[2] === 'rate') {
        $rat = new RatingsController();
        $rat->rate((int)$parts[1]);
        exit;
    }
    if ($method === 'GET' && count($parts) === 3 && $parts[2] === 'rating') {
        $rat = new RatingsController();
        $rat->aggregate((int)$parts[1]);
        exit;
    }

    // Reports
    if ($method === 'POST' && count($parts) === 3 && $parts[2] === 'report') {
        $rep = new ReportsController();
        $rep->report((int)$parts[1]);
        exit;
    }
}

// Endpoint: Estado de salud de la API
// Health / status endpoint (aceptar variantes con prefijos)
if ($uri === '/api/status' || str_ends_with($uri, '/api/status')) {
    $expectedStatusToken = trim((string)Config::get('STATUS_TOKEN', ''));
    if ($expectedStatusToken !== '') {
        $providedStatusToken = trim((string)($_SERVER['HTTP_X_STATUS_TOKEN'] ?? ''));
        if ($providedStatusToken === '' || !hash_equals($expectedStatusToken, $providedStatusToken)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'forbidden']);
            exit;
        }
    }

    try {
        $statusVerbose = Config::getBool('STATUS_VERBOSE', false);
        $dbMode = DatabaseAdapter::useSupabase() ? 'supabase' : 'local';
        if (DatabaseAdapter::useSupabase()) {
            $dbConnected = true; // Supabase credenciales cargadas correctamente
            $connectionInfo = [
                'host' => parse_url(Config::get('SUPABASE_URL', ''), PHP_URL_HOST),
                'database' => 'supabase:spots'
            ];
        } else {
            $dbConnected = Database::isConnected();
            $connectionInfo = Database::getConnectionInfo();
        }
        $status = $dbConnected ? 'healthy' : 'degraded';
        
        http_response_code($dbConnected ? 200 : 503);
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        echo json_encode([
            'status' => $status,
            'api_version' => Config::get('API_VERSION', '1.0.0'),
            'environment' => Config::get('ENV', 'development'),
            'timestamp' => date('c'),
            'database' => [
                'mode' => $dbMode,
                'connected' => $dbConnected,
                'host' => $statusVerbose ? ($connectionInfo['host'] ?? null) : null,
                'database' => $connectionInfo['database'] ?? null,
            ],
            // Expose only non-sensitive operational flags.
            'features' => [
                'ownership_enabled' => Config::getBool('OWNERSHIP_ENABLED', false),
                'metrics_enabled' => Config::getBool('METRICS_ENABLED', false),
                'rate_limit_enabled' => Config::getBool('RATE_LIMIT_ENABLED', false),
            ],
        ]);
    } catch (\Exception $e) {
        Logger::exception($e, 'Status endpoint error');
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'error' => Config::isDebug() ? $e->getMessage() : 'Internal status error',
        ]);
    }
    exit;
}

// Metrics endpoint (simple JSON) if enabled
if ($uri === '/api/metrics') {
    if (!Config::getBool('METRICS_ENABLED', false)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Metrics disabled']);
        exit;
    }
    $stats = Cache::stats();
    $metrics = array_merge([
        'generated_at' => time(),
        'env' => Config::get('ENV'),
    ], $stats, Metrics::all());
    header('Content-Type: application/json');
    echo json_encode($metrics);
    exit;
}

// Prometheus plaintext metrics
if ($uri === '/api/metrics/prometheus') {
    if (!Config::getBool('METRICS_ENABLED', false)) {
        http_response_code(404);
        header('Content-Type: text/plain');
        echo "metrics_disabled 1";
        exit;
    }
    $stats = Cache::stats();
    $all = Metrics::all();
    header('Content-Type: text/plain');
    echo "# HELP spotmap_requests_total Total requests\n";
    echo "# TYPE spotmap_requests_total counter\n";
    echo "spotmap_requests_total {$all['requests_total']}\n";
    foreach ($all as $k=>$v) {
        if (strpos($k,'requests_')===0 && $k!=='requests_total') {
            echo "spotmap_{$k} {$v}\n";
        }
    }
    echo "spotmap_latency_count {$all['latency_count']}\n";
    echo "spotmap_latency_sum_ms {$all['latency_sum_ms']}\n";
    echo "spotmap_latency_avg_ms {$all['latency_avg_ms']}\n";
    echo "spotmap_latency_max_ms {$all['latency_max_ms']}\n";
    echo "spotmap_latency_min_ms {$all['latency_min_ms']}\n";
    echo "spotmap_cache_hits {$stats['hits']}\n";
    echo "spotmap_cache_misses {$stats['misses']}\n";
    exit;
}

// Admin reports listing
if (($uri === '/api/admin/reports' || str_ends_with($uri, '/api/admin/reports')) && $method === 'GET') {
    $rep = new ReportsController();
    $rep->list();
    exit;
}
// Admin report moderation
if ($method === 'POST' && preg_match('#/api/admin/reports/(\d+)$#', $uri)) {
    $reportId = (int)preg_replace('#^.*?/api/admin/reports/(\d+)$#', '$1', $uri);
    $rep = new ReportsController();
    $rep->moderate($reportId);
    exit;
}
// Account export
if ($uri === '/api/account/export' && $method === 'GET') {
    Metrics::inc('requests_account_export');
    $acc = new AccountController();
    $acc->export();
    exit;
}
// Account delete
if ($uri === '/api/account/delete' && $method === 'POST') {
    Metrics::inc('requests_account_delete');
    $acc = new AccountController();
    $acc->delete();
    exit;
}
// Admin global stats
if (($uri === '/api/admin/stats' || str_ends_with($uri, '/api/admin/stats')) && $method === 'GET') {
    $adm = new AdminController();
    $adm->stats();
    exit;
}

// Admin pending spots
if (($uri === '/api/admin/pending' || str_ends_with($uri, '/api/admin/pending')) && $method === 'GET') {
    $adm = new AdminController();
    $adm->pendingSpots();
    exit;
}

// Admin approve spot
if ($method === 'POST' && preg_match('#/api/admin/spots/(\d+)/approve$#', $uri)) {
    $spotId = (int)preg_replace('#^.*?/api/admin/spots/(\d+)/approve$#', '$1', $uri);
    $adm = new AdminController();
    $adm->approveSpot($spotId);
    exit;
}

// Admin reject spot
if ($method === 'POST' && preg_match('#/api/admin/spots/(\d+)/reject$#', $uri)) {
    $spotId = (int)preg_replace('#^.*?/api/admin/spots/(\d+)/reject$#', '$1', $uri);
    $adm = new AdminController();
    $adm->rejectSpot($spotId);
    exit;
}
// ============================================
// NOTIFICATIONS ROUTES
// ============================================

// Get user notifications
if (($uri === '/api/notifications' || str_ends_with($uri, '/api/notifications')) && $method === 'GET') {
    $notif = new NotificationController();
    $notif->index();
    exit;
}

// Get unread count
if (preg_match('#/api/notifications/unread-count$#', $uri) && $method === 'GET') {
    $notif = new NotificationController();
    $notif->unreadCount();
    exit;
}

// Mark notification as read
if ($method === 'PATCH' && preg_match('#/api/notifications/(\d+)/read$#', $uri)) {
    $notifId = (int)preg_replace('#^.*?/api/notifications/(\d+)/read$#', '$1', $uri);
    $notif = new NotificationController();
    $notif->markAsRead($notifId);
    exit;
}

// Mark all as read
if ($method === 'POST' && preg_match('#/api/notifications/mark-all-read$#', $uri)) {
    $notif = new NotificationController();
    $notif->markAllAsRead();
    exit;
}

// Delete notification
if ($method === 'DELETE' && preg_match('#/api/notifications/(\d+)$#', $uri)) {
    $notifId = (int)preg_replace('#^.*?/api/notifications/(\d+)$#', '$1', $uri);
    $notif = new NotificationController();
    $notif->delete($notifId);
    exit;
}


http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error'=>'Route not found']);

// Latency recording on shutdown
register_shutdown_function(function() use ($__req_start) {
    $elapsed = (microtime(true) - $__req_start) * 1000.0; // ms
    \SpotMap\Metrics::recordDuration($elapsed);
});


