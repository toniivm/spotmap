<?php
declare(strict_types=1);
namespace SpotMap;

/**
 * 🔐 Security Consolidada - Sistema unificado de seguridad
 * Integra: CORS, CSP, sanitización, rate limiting, CSRF, encryption
 * Absorbió funcionalidades de SecurityHardening para evitar duplicación
 */
class Security
{
    private static ?string $nonce = null;
    private static array $suspiciousIPs = [];
    private static array $blockedIPs = [];
    private static int $maxRequestsPerMinute = 60;
    
    /**
     * Configurar headers CORS
     */
    public static function setCORSHeaders($allowedOrigin = null)
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowCredentials = false;

        if ($allowedOrigin) {
            if ($origin !== $allowedOrigin) {
                header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS, PUT");
                header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
                header("Access-Control-Max-Age: 86400");
                return;
            }
            $allowCredentials = true;
        } else {
            if ($origin !== '') {
                $allowCredentials = true;
            }
        }

        $allowOrigin = $origin !== '' ? $origin : '*';
        header("Access-Control-Allow-Origin: $allowOrigin");
        header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS, PUT");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        if ($allowCredentials && $allowOrigin !== '*') {
            header("Access-Control-Allow-Credentials: true");
        }
        header("Access-Control-Max-Age: 86400");
    }

    /**
     * Configurar headers de seguridad completos
     */
    public static function setSecurityHeaders()
    {
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }
        
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(self), camera=()");
        header("X-Permitted-Cross-Domain-Policies: none");

        // CSP dinámica
        Config::load();
        $nonce = self::getNonce();
        $csp = [
            'default-src ' . Config::get('CSP_DEFAULT', "'self'"),
            'script-src ' . Config::get('CSP_SCRIPT', "'self'") . " 'nonce-$nonce'",
            'style-src ' . Config::get('CSP_STYLE', "'self'"),
            'img-src ' . Config::get('CSP_IMG', "'self'"),
            'font-src ' . Config::get('CSP_FONT', "'self'"),
            'connect-src ' . Config::get('CSP_CONNECT', "'self'"),
            'object-src ' . Config::get('CSP_OBJECT', "'none'"),
            'base-uri ' . Config::get('CSP_BASE', "'self'"),
            'frame-ancestors ' . Config::get('CSP_FRAME_ANCESTORS', "'none'"),
        ];
        header('Content-Security-Policy: ' . implode('; ', $csp));
        header('X-CSP-Nonce: ' . $nonce);
        header('X-SpotMap-Protected: true');
    }

    /**
     * Headers de seguridad avanzados (complementario)
     */
    public static function setAdvancedSecurityHeaders(): void
    {
        self::setSecurityHeaders();
        header('X-Copyright: (c) 2025 Antonio Valero. Todos los derechos reservados.');
    }

    /**
     * Sanitizar string para prevenir inyección
     */
    public static function sanitizeString($input)
    {
        if (!is_string($input)) {
            return $input;
        }

        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $input);
        $sanitized = trim($sanitized);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        
        return $sanitized;
    }

    /**
     * Sanitizar array recursivamente
     */
    public static function sanitizeArray($input)
    {
        if (!is_array($input)) {
            return [self::sanitizeString($input)];
        }

        $sanitized = [];
        foreach ($input as $key => $value) {
            $key = self::sanitizeString($key);
            $value = is_array($value) ? self::sanitizeArray($value) : self::sanitizeString($value);
            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * Sanitización avanzada de inputs (múltiples tipos)
     */
    public static function sanitizeInput(mixed $input, string $type = 'string'): mixed
    {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $input);
        }

        switch ($type) {
            case 'string':
                $input = strip_tags($input);
                $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                break;
            case 'email':
                $input = filter_var($input, FILTER_SANITIZE_EMAIL);
                break;
            case 'url':
                $input = filter_var($input, FILTER_SANITIZE_URL);
                break;
            case 'int':
                $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                break;
            case 'float':
                $input = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;
            case 'sql':
                $input = str_replace(["'", '"', ';', '--', '/*', '*/'], '', $input);
                break;
        }

        // Detectar patrones de ataque
        $dangerousPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/eval\s*\(/i',
            '/UNION.*SELECT/i',
            '/DROP.*TABLE/i',
            '/INSERT.*INTO/i',
            '/UPDATE.*SET/i',
            '/DELETE.*FROM/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                self::flagSuspiciousIP(self::getClientIP(), 'injection_attempt');
                throw new \Exception('Input contains potentially malicious content');
            }
        }

        return $input;
    }

    /**
     * Rate limiting por IP
     */
    public static function checkRateLimit($maxRequests = 100, $timeWindow = 60): bool
    {
        $ip = self::getClientIP();
        
        // Verificar si IP está bloqueada
        if (self::isIPBlocked($ip)) {
            return false;
        }

        $cacheKey = "rate_limit_" . md5($ip);
        $cacheFile = sys_get_temp_dir() . '/' . $cacheKey . '.tmp';
        $now = time();

        $data = [];
        if (file_exists($cacheFile)) {
            $content = file_get_contents($cacheFile);
            $data = json_decode($content, true) ?? [];
        }

        // Limpiar requests antiguos
        $data['requests'] = array_filter($data['requests'] ?? [], function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });

        // Verificar límite
        if (count($data['requests']) >= $maxRequests) {
            header("HTTP/1.1 429 Too Many Requests");
            header("Retry-After: " . $timeWindow);
            header("X-RateLimit-Limit: $maxRequests");
            header("X-RateLimit-Remaining: 0");
            header("X-RateLimit-Reset: " . ($now + $timeWindow));
            self::flagSuspiciousIP($ip, 'rate_limit_exceeded');
            return false;
        }

        // Agregar request actual
        $data['requests'][] = $now;
        @file_put_contents($cacheFile, json_encode($data));

        // Headers informativos
        header("X-RateLimit-Limit: $maxRequests");
        header("X-RateLimit-Remaining: " . max(0, $maxRequests - count($data['requests'])));
        header("X-RateLimit-Reset: " . ($now + $timeWindow));

        return true;
    }

    /**
     * Protección CSRF - Generar token
     */
    public static function generateCSRFToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_time'] = time();

        return $token;
    }

    /**
     * Protección CSRF - Validar token
     */
    public static function validateCSRFToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_time'])) {
            return false;
        }

        // Token expira en 1 hora
        if (time() - $_SESSION['csrf_time'] > 3600) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_time']);
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generar nonce para CSP
     */
    public static function generateNonce(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Obtener nonce actual (o crear uno)
     */
    public static function getNonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = self::generateNonce();
        }
        return self::$nonce;
    }

    /**
     * Validar nonce
     */
    public static function validateNonce($nonce): bool
    {
        return is_string($nonce) && strlen($nonce) === 32 && ctype_xdigit($nonce);
    }

    /**
     * Obtener IP real del cliente (detrás de proxies/CDN)
     */
    public static function getClientIP(): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Encriptar datos (AES-256-CBC)
     */
    public static function encrypt(string $data, string $key): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            hash('sha256', $key, true),
            0,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    /**
     * Desencriptar datos
     */
    public static function decrypt(string $data, string $key): string
    {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            hash('sha256', $key, true),
            0,
            $iv
        );
    }

    /**
     * Marcar IP como sospechosa
     */
    private static function flagSuspiciousIP(string $ip, string $reason): void
    {
        if (!isset(self::$suspiciousIPs[$ip])) {
            self::$suspiciousIPs[$ip] = [];
        }

        self::$suspiciousIPs[$ip][] = [
            'reason' => $reason,
            'timestamp' => time()
        ];

        Logger::warning("Actividad sospechosa detectada", [
            'ip' => $ip,
            'reason' => $reason,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        // Bloquear después de 3 infracciones
        if (count(self::$suspiciousIPs[$ip]) >= 3) {
            self::blockIP($ip);
        }
    }

    /**
     * Bloquear IP
     */
    private static function blockIP(string $ip): void
    {
        self::$blockedIPs[] = $ip;

        $blockedFile = __DIR__ . '/../../config/blocked_ips.txt';
        @mkdir(dirname($blockedFile), 0755, true);
        @file_put_contents($blockedFile, $ip . PHP_EOL, FILE_APPEND | LOCK_EX);

        Logger::error("IP bloqueada permanentemente", ['ip' => $ip]);
    }

    /**
     * Verificar si IP está bloqueada
     */
    private static function isIPBlocked(string $ip): bool
    {
        $blockedFile = __DIR__ . '/../../config/blocked_ips.txt';
        if (file_exists($blockedFile)) {
            $blockedIPs = @file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            return in_array($ip, $blockedIPs, true);
        }

        return in_array($ip, self::$blockedIPs, true);
    }

    /**
     * Validar JSON válido
     */
    public static function isValidJSON(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    /**
     * Registrar acceso (logging simple)
     */
    public static function logAccess($method, $endpoint, $statusCode)
    {
        $logDir = __DIR__ . '/../../logs';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $ip = self::getClientIP();
        $timestamp = date('Y-m-d H:i:s');
        $logFile = $logDir . '/api_' . date('Y-m-d') . '.log';

        $logLine = "[$timestamp] $method $endpoint | Status: $statusCode | IP: $ip\n";
        @file_put_contents($logFile, $logLine, FILE_APPEND);
    }

    /**
     * Validar fingerprint del cliente
     */
    public static function validateClientFingerprint(): bool
    {
        $fingerprint = $_SERVER['HTTP_X_SPOTMAP_FINGERPRINT'] ?? null;

        if (!$fingerprint) {
            return false;
        }

        if (!preg_match('/^[a-z0-9]{8,16}$/', $fingerprint)) {
            self::flagSuspiciousIP(self::getClientIP(), 'invalid_fingerprint');
            return false;
        }

        return true;
    }
}
