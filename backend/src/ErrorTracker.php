<?php
/**
 * ⚠️ SPOTMAP - ERROR TRACKING & REPORTING
 * Copyright (c) 2025 Antonio Valero. Todos los derechos reservados.
 */

namespace SpotMap;

class ErrorTracker {
    private static $errors = [];
    private static $instance = null;
    private $logger;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->logger = Logger::getInstance();
        
        // Register error handler
        set_error_handler([$this, 'handleError']);
        
        // Register exception handler
        set_exception_handler([$this, 'handleException']);
        
        // Register fatal error handler
        register_shutdown_function([$this, 'handleFatalError']);
    }

    /**
     * Handle errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        $errorType = $this->getErrorType($errno);
        
        $context = [
            'file' => $errfile,
            'line' => $errline,
            'errno' => $errno,
            'type' => $errorType
        ];

        // Log based on severity
        if ($errno === E_ERROR || $errno === E_PARSE) {
            $this->logger->critical($errstr, $context);
        } elseif ($errno === E_WARNING || $errno === E_USER_ERROR) {
            $this->logger->error($errstr, $context);
        } elseif ($errno === E_NOTICE || $errno === E_USER_WARNING) {
            $this->logger->warning($errstr, $context);
        } else {
            $this->logger->debug($errstr, $context);
        }

        // Store error
        self::$errors[] = [
            'type' => $errorType,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => time()
        ];

        return true; // Don't use PHP's default error handler
    }

    /**
     * Handle exceptions
     */
    public function handleException(\Throwable $e) {
        $debug = getenv('APP_DEBUG') === 'true';
        if (class_exists('\\SpotMap\\Config')) {
            $debug = $debug || (bool)\SpotMap\Config::get('DEBUG', false);
        }
        $context = [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];

        $this->logger->error($e->getMessage(), $context);

        // Store error
        self::$errors[] = [
            'type' => 'Exception',
            'message' => $e->getMessage(),
            'class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'timestamp' => time()
        ];

        $errors = $debug ? [
            'detail' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ] : null;

        if (class_exists('\\SpotMap\\ApiResponse')) {
            \SpotMap\ApiResponse::serverError('Internal Server Error', $errors);
            return;
        }

        if (ob_get_level() > 0) {
            ob_clean();
        }
        http_response_code(500);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'error' => 'Internal Server Error',
            'code' => 500,
            'message' => $debug ? $e->getMessage() : null
        ]);
    }

    /**
     * Handle fatal errors
     */
    public function handleFatalError() {
        $error = error_get_last();
        
        if ($error === null) {
            return;
        }

        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
        if (in_array($error['type'], $fatalTypes, true)) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    /**
     * Get error type name
     */
    private function getErrorType($errno) {
        $types = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];

        return $types[$errno] ?? 'Unknown Error';
    }

    /**
     * Report error to external service
     */
    public static function reportError($error, $context = []) {
        $errorService = getenv('ERROR_TRACKING_SERVICE');
        if (!$errorService) {
            return;
        }

        $payload = [
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => getenv('APP_ENV') ?: 'production',
            'error' => $error,
            'context' => $context,
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                'path' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $errorService);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Get all errors
     */
    public static function getErrors() {
        return self::$errors;
    }

    /**
     * Clear errors
     */
    public static function clearErrors() {
        self::$errors = [];
    }
}

// Auto-initialize
ErrorTracker::getInstance();
