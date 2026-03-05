<?php
/**
 * ⚠️ SPOTMAP - PRODUCTION CONFIGURATION
 * Copyright (c) 2025 Antonio Valero. Todos los derechos reservados.
 * PROHIBIDA SU COPIA, MODIFICACIÓN O DISTRIBUCIÓN SIN AUTORIZACIÓN.
 */

namespace SpotMap\Config;

class ProductionConfig {
    /**
     * Load and validate environment variables
     */
    public static function loadEnv() {
        // Load .env.production file
        $envFile = dirname(__DIR__, 2) . '/.env.production';
        
        if (!file_exists($envFile)) {
            self::error('FATAL: .env.production not found. Cannot start application.');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '=') === false || $line[0] === '#') continue;
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Don't override if already set
            if (empty($_ENV[$key])) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }

        return true;
    }

    /**
     * Get environment variable with fallback
     */
    public static function get($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    /**
     * Validate required environment variables
     */
    public static function validate() {
        $required = [
            'VITE_SUPABASE_URL',
            'VITE_SUPABASE_ANON_KEY',
            'VITE_API_URL',
            'DB_HOST',
            'DB_NAME',
            'DB_USER'
        ];

        foreach ($required as $var) {
            if (!getenv($var)) {
                self::error("FATAL: Required environment variable '{$var}' is not set");
            }
        }

        return true;
    }

    /**
     * Get HTTPS certificate paths
     */
    public static function getSSLConfig() {
        return [
            'cert_path' => '/etc/ssl/certs/spotmap.crt',
            'key_path' => '/etc/ssl/private/spotmap.key',
            'ca_path' => '/etc/ssl/certs/ca-bundle.crt',
            'hsts_max_age' => 31536000, // 1 year
            'hsts_include_subdomains' => true,
            'hsts_preload' => true
        ];
    }

    /**
     * Get CSP (Content Security Policy) headers
     */
    public static function getCSPHeaders() {
        return [
            "default-src 'self'",
            "script-src 'self' 'wasm-unsafe-eval'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            "connect-src 'self' " . self::get('VITE_SUPABASE_URL') . " " . self::get('VITE_API_URL'),
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests"
        ];
    }

    /**
     * Get CORS allowed origins
     */
    public static function getCORSOrigins() {
        return [
            self::get('VITE_API_URL'),
            self::get('VITE_SUPABASE_URL')
        ];
    }

    /**
     * Get security headers
     */
    public static function getSecurityHeaders() {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
            'Strict-Transport-Security' => 'max-age=' . self::getSSLConfig()['hsts_max_age'] . '; includeSubDomains; preload'
        ];
    }

    /**
     * Get logging configuration
     */
    public static function getLogConfig() {
        return [
            'level' => self::get('BACKEND_LOG_LEVEL', 'error'),
            'file' => dirname(__DIR__) . '/logs/production.log',
            'max_size' => 10485760, // 10MB
            'max_files' => 10,
            'filter_sensitive' => true
        ];
    }

    /**
     * Error handler
     */
    private static function error($message) {
        error_log($message);
        if (php_sapi_name() === 'cli') {
            echo "❌ ERROR: {$message}\n";
        } else {
            http_response_code(503);
            echo "Service Unavailable";
        }
        exit(1);
    }
}

// Auto-load on require
ProductionConfig::loadEnv();
