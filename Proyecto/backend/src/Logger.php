<?php
declare(strict_types=1);
namespace SpotMap;

/**
 * 🔍 Logger Consolidado - Sistema unificado de logging
 * Integra funcionalidades de Logger simple y AdvancedLogger
 * Proporciona: archivo, rotación, seguridad, métricas y alertas
 */
class Logger
{
    // Niveles de log
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';
    const LEVEL_SECURITY = 'SECURITY';

    private static array $levels = [
        self::LEVEL_DEBUG => 0,
        self::LEVEL_INFO => 1,
        self::LEVEL_WARNING => 2,
        self::LEVEL_ERROR => 3,
        self::LEVEL_CRITICAL => 4,
        self::LEVEL_SECURITY => 4,
    ];

    private static ?string $requestId = null;
    private static bool $initialized = false;
    private static ?self $instance = null;
    
    private string $logFile;
    private string $metricsFile;
    private string $alertsFile;
    private int $maxLogSize = 10485760; // 10MB
    private int $maxLogFiles = 10;
    private array $sensitivePatterns = [
        '/password\s*[=:]\s*[^\s,}]+/i',
        '/apikey\s*[=:]\s*[^\s,}]+/i',
        '/token\s*[=:]\s*[^\s,}]+/i',
        '/authorization\s*[=:]\s*[^\s,}]+/i',
        '/(credit.?card|cvv|ssn|bearer)\s*[=:]\s*[^\s,}]+/i',
    ];

    /**
     * Obtener instancia singleton para métodos de instancia
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado (singleton)
     */
    private function __construct()
    {
        self::init();
        
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $this->logFile = $logDir . '/application.log';
        $this->metricsFile = $logDir . '/metrics.json';
        $this->alertsFile = $logDir . '/alerts.log';
    }

    /**
     * Inicializar sistema de logging
     */
    private static function init(): void
    {
        if (self::$initialized) return;
        
        self::$requestId = self::generateRequestId();
        self::$initialized = true;
    }

    /**
     * Generar ID único para la request
     */
    private static function generateRequestId(): string
    {
        return $_SERVER['HTTP_X_REQUEST_ID'] 
            ?? bin2hex(random_bytes(6));
    }

    public static function getRequestId(): string
    {
        self::init();
        return self::$requestId;
    }

    /**
     * Determinar si se debe loguear según nivel
     */
    private static function shouldLog(string $level): bool
    {
        $configLevel = Config::get('LOG_LEVEL', 'INFO');
        $configLevel = strtoupper($configLevel);
        return (self::$levels[$level] ?? 999) >= (self::$levels[$configLevel] ?? 1);
    }

    /**
     * Sanitizar datos sensibles de mensajes
     */
    private function sanitizeData(string $data): string
    {
        foreach ($this->sensitivePatterns as $pattern) {
            $data = preg_replace($pattern, '[REDACTED]', $data);
        }
        return $data;
    }

    /**
     * Sanitizar contexto (quitar claves sensibles)
     */
    private function sanitizeContext(array $context): array
    {
        $sensitiveKeys = ['password', 'token', 'apikey', 'secret', 'key', 'authorization', 'bearer'];
        
        foreach ($context as $key => &$value) {
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $value = '[REDACTED]';
            } elseif (is_string($value)) {
                $value = $this->sanitizeData($value);
            }
        }
        return $context;
    }

    /**
     * Crear entrada de log formateada
     */
    private function createLogEntry(string $level, string $message, array $context = []): array
    {
        self::init();
        
        $sanitized = $this->sanitizeContext($context);
        
        return [
            'timestamp' => date('Y-m-d H:i:s.') . substr((string)microtime(true), -4),
            'level' => $level,
            'message' => $this->sanitizeData($message),
            'context' => $sanitized,
            'request_id' => self::$requestId,
            'env' => Config::get('ENV', 'development'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
        ];
    }

    /**
     * Escribir log a archivo con rotación
     */
    private function writeToFile(string $filePath, array $logEntry): void
    {
        // Rotación automática si archivo es muy grande
        if (file_exists($filePath) && filesize($filePath) > $this->maxLogSize) {
            $this->rotateLog($filePath);
        }

        $line = json_encode($logEntry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        @file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotar archivos de log cuando alcanzan tamaño máximo
     */
    private function rotateLog(string $filePath): void
    {
        $dir = dirname($filePath);
        $basename = basename($filePath);
        
        // Eliminar archivo más antiguo si alcanzó límite
        for ($i = $this->maxLogFiles; $i >= 1; $i--) {
            $oldFile = "$dir/$basename.$i";
            if (file_exists($oldFile)) {
                if ($i === $this->maxLogFiles) {
                    @unlink($oldFile);
                } else {
                    @rename($oldFile, "$dir/$basename." . ($i + 1));
                }
            }
        }

        @rename($filePath, "$dir/$basename.1");
    }

    /**
     * Registrar log con nivel especificado (método centralizado)
     */
    public function log(string $level, string $message, array $context = []): void
    {
        if (!self::shouldLog($level)) {
            return;
        }

        $logEntry = $this->createLogEntry($level, $message, $context);
        $this->writeToFile($this->logFile, $logEntry);

        // Log to system error_log también
        if (in_array($level, [self::LEVEL_ERROR, self::LEVEL_CRITICAL], true)) {
            error_log(json_encode($logEntry));
        }

        // Crear alerta si es necesario
        if ($this->shouldAlert($level, $context)) {
            $this->createAlert($level, $message, $context);
        }
    }

    /**
     * Métodos estáticos para logging directo (conveniencia)
     */
    public static function debug(string $message, ?array $context = null): void
    {
        if (Config::get('DEBUG', false)) {
            self::getInstance()->log(self::LEVEL_DEBUG, $message, $context ?? []);
        }
    }

    public static function info(string $message, ?array $context = null): void
    {
        self::getInstance()->log(self::LEVEL_INFO, $message, $context ?? []);
    }

    public static function warning(string $message, ?array $context = null): void
    {
        self::getInstance()->log(self::LEVEL_WARNING, $message, $context ?? []);
    }

    public static function warn(string $message, ?array $context = null): void
    {
        self::warning($message, $context);
    }

    public static function error(string $message, ?array $context = null): void
    {
        self::getInstance()->log(self::LEVEL_ERROR, $message, $context ?? []);
    }

    public static function critical(string $message, ?array $context = null): void
    {
        self::getInstance()->log(self::LEVEL_CRITICAL, $message, $context ?? []);
    }

    public static function security(string $message, ?array $context = null): void
    {
        $ctx = $context ?? [];
        $ctx['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $ctx['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
        self::getInstance()->log(self::LEVEL_SECURITY, $message, $ctx);
    }

    public static function exception(\Throwable $e, string $prefix = 'Exception'): void
    {
        self::error("$prefix: {$e->getMessage()}", [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => Config::get('DEBUG', false) ? $e->getTraceAsString() : null,
        ]);
    }

    /**
     * Registrar métrica de API
     */
    public function logMetric(string $endpoint, string $method, int $statusCode, float $responseTime, int $memoryUsage): void
    {
        $metric = [
            'timestamp' => microtime(true),
            'endpoint' => $endpoint,
            'method' => $method,
            'status' => $statusCode,
            'response_time_ms' => round($responseTime * 1000, 2),
            'memory_mb' => round($memoryUsage / 1048576, 2),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI'
        ];

        $metrics = [];
        if (file_exists($this->metricsFile)) {
            $content = @file_get_contents($this->metricsFile);
            $metrics = json_decode($content, true) ?? [];
        }

        $metrics[] = $metric;
        // Mantener últimas 1000 métricas
        if (count($metrics) > 1000) {
            array_shift($metrics);
        }

        @file_put_contents($this->metricsFile, json_encode($metrics, JSON_PRETTY_PRINT), LOCK_EX);
    }

    /**
     * Determinar si crear alerta
     */
    private function shouldAlert(string $level, array $context): bool
    {
        return in_array($level, [self::LEVEL_CRITICAL, self::LEVEL_SECURITY], true);
    }

    /**
     * Crear alerta y enviar notificaciones
     */
    private function createAlert(string $level, string $message, array $context): void
    {
        $alert = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $this->sanitizeContext($context)
        ];

        $alertLine = json_encode($alert, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        @file_put_contents($this->alertsFile, $alertLine, FILE_APPEND | LOCK_EX);

        // Enviar email para críticos
        if ($level === self::LEVEL_CRITICAL) {
            $this->sendEmailAlert($alert);
        }

        // Enviar webhook si está configurado
        $this->sendWebhookAlert($alert);
    }

    /**
     * Enviar alerta por email
     */
    private function sendEmailAlert(array $alert): void
    {
        $alertEmail = Config::get('ALERT_EMAIL');
        if (!$alertEmail) return;

        $subject = "🚨 SpotMap Alert: {$alert['level']}";
        $body = "Critical Alert!\n\n";
        $body .= "Message: {$alert['message']}\n";
        $body .= "Time: {$alert['timestamp']}\n";
        $body .= "Context: " . json_encode($alert['context'], JSON_PRETTY_PRINT);

        $headers = "From: noreply@spotmap.local\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        @mail($alertEmail, $subject, $body, $headers);
    }

    /**
     * Enviar alerta por webhook
     */
    private function sendWebhookAlert(array $alert): void
    {
        $webhookUrl = Config::get('ALERT_WEBHOOK_URL');
        if (!$webhookUrl) return;

        $payload = json_encode([
            'type' => 'spotmap_alert',
            'level' => $alert['level'],
            'message' => $alert['message'],
            'timestamp' => $alert['timestamp'],
            'details' => $alert['context']
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhookUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Obtener logs del archivo
     */
    public function getLogs(int $limit = 100, ?string $level = null): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $lines = @file($this->logFile, FILE_IGNORE_NEW_LINES) ?: [];
        $logs = [];

        foreach (array_reverse($lines) as $line) {
            if (count($logs) >= $limit) break;
            
            $log = json_decode($line, true);
            if ($log === null) continue;

            if ($level && $log['level'] !== $level) continue;

            $logs[] = $log;
        }

        return array_reverse($logs);
    }

    /**
     * Obtener resumen de métricas
     */
    public function getMetricsSummary(): array
    {
        if (!file_exists($this->metricsFile)) {
            return [];
        }

        $content = @file_get_contents($this->metricsFile);
        $metrics = json_decode($content, true) ?? [];

        if (empty($metrics)) {
            return [];
        }

        $summary = [
            'total_requests' => count($metrics),
            'avg_response_time_ms' => 0,
            'avg_memory_mb' => 0,
            'error_count' => 0,
            'status_codes' => []
        ];

        $totalTime = 0;
        $totalMemory = 0;

        foreach ($metrics as $metric) {
            $totalTime += $metric['response_time_ms'];
            $totalMemory += $metric['memory_mb'];

            if ($metric['status'] >= 400) {
                $summary['error_count']++;
            }

            $code = $metric['status'];
            $summary['status_codes'][$code] = ($summary['status_codes'][$code] ?? 0) + 1;
        }

        $summary['avg_response_time_ms'] = round($totalTime / count($metrics), 2);
        $summary['avg_memory_mb'] = round($totalMemory / count($metrics), 2);

        return $summary;
    }

    /**
     * Obtener alertas
     */
    public function getAlerts(int $limit = 50): array
    {
        if (!file_exists($this->alertsFile)) {
            return [];
        }

        $lines = @file($this->alertsFile, FILE_IGNORE_NEW_LINES) ?: [];
        $alerts = [];

        foreach (array_reverse($lines) as $line) {
            if (count($alerts) >= $limit) break;
            
            $alert = json_decode($line, true);
            if ($alert !== null) {
                $alerts[] = $alert;
            }
        }

        return array_reverse($alerts);
    }
}
