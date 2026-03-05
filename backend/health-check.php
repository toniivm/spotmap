<?php
/**
 * ⚠️ CÓDIGO PROPIETARIO - SPOTMAP ⚠️
 * Health Check Automático - Para uso en cron o monitoreo
 * Copyright (c) 2025 Antonio Valero
 */
declare(strict_types=1);

require __DIR__ . '/src/Config.php';
require __DIR__ . '/src/AdvancedLogger.php';
require __DIR__ . '/src/Database.php';
require __DIR__ . '/src/DatabaseAdapter.php';

use SpotMap\AdvancedLogger;
use SpotMap\Database;
use SpotMap\DatabaseAdapter;

// Cargar configuración
\SpotMap\Config::load();
$logger = Logger::getInstance();

// Color output
class Health {
    const HEALTHY = 'healthy';
    const WARNING = 'warning';
    const CRITICAL = 'critical';
}

$checks = [];
$timestamp = date('Y-m-d H:i:s');

echo "╔═══════════════════════════════════════════╗\n";
echo "║  SpotMap - Health Check Report            ║\n";
echo "║  " . date('Y-m-d H:i:s') . "                    ║\n";
echo "╚═══════════════════════════════════════════╝\n\n";

// 1. Verificar CPU y memoria
echo "🖥️  Sistema\n";
echo "─" . str_repeat("─", 40) . "\n";

$memoryUsage = memory_get_usage(true);
$memoryPeak = memory_get_peak_usage(true);
$memoryLimit = ini_get('memory_limit');

$uptime = 'N/A';
if (function_exists('exec') && !str_contains(php_uname(), 'Windows')) {
    try {
        $uptime = trim(exec('uptime'));
    } catch (Exception $e) {
        // Silent fail
    }
}

echo "Memoria actual: " . round($memoryUsage / 1024 / 1024, 2) . " MB\n";
echo "Memoria pico:   " . round($memoryPeak / 1024 / 1024, 2) . " MB\n";
echo "Límite PHP:     " . $memoryLimit . "\n";
echo "Tiempo activo:  " . $uptime . "\n";

$checks['system'] = [
    'status' => Health::HEALTHY,
    'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
    'memory_peak_mb' => round($memoryPeak / 1024 / 1024, 2),
    'memory_limit' => $memoryLimit
];

// 2. Verificar base de datos
echo "\n💾 Base de Datos\n";
echo "─" . str_repeat("─", 40) . "\n";

$dbStatus = Health::CRITICAL;
$dbMessage = 'No configurada';

try {
    if (DatabaseAdapter::useSupabase()) {
        $dbMessage = 'Supabase';
    } else {
        Database::init();
        // Intenta una query simple
        try {
            $result = Database::query("SELECT 1");
            $dbStatus = Health::HEALTHY;
            $dbMessage = 'PDO local conectada';
        } catch (Exception $e) {
            $dbStatus = Health::CRITICAL;
            $dbMessage = 'Error de conexión: ' . $e->getMessage();
        }
    }
} catch (Exception $e) {
    $dbStatus = Health::CRITICAL;
    $dbMessage = 'Error: ' . $e->getMessage();
}

echo "Estado:     " . strtoupper($dbStatus) . "\n";
echo "Tipo:       " . $dbMessage . "\n";

$checks['database'] = [
    'status' => $dbStatus,
    'type' => $dbMessage
];

// 3. Verificar almacenamiento
echo "\n📁 Almacenamiento\n";
echo "─" . str_repeat("─", 40) . "\n";

$logDir = __DIR__ . '/logs';
$uploadDir = __DIR__ . '/public/uploads';

$logDirExists = is_dir($logDir);
$uploadDirExists = is_dir($uploadDir);

$logDirSize = 0;
if ($logDirExists) {
    $files = glob("$logDir/*");
    foreach ($files as $file) {
        if (is_file($file)) {
            $logDirSize += filesize($file);
        }
    }
}

$uploadDirSize = 0;
if ($uploadDirExists) {
    $files = glob("$uploadDir/*");
    foreach ($files as $file) {
        if (is_file($file)) {
            $uploadDirSize += filesize($file);
        }
    }
}

$logDirStatus = $logDirExists ? Health::HEALTHY : Health::WARNING;
$uploadDirStatus = $uploadDirExists ? Health::HEALTHY : Health::WARNING;

echo "Logs:       " . strtoupper($logDirStatus) . " (" . round($logDirSize / 1024 / 1024, 2) . " MB)\n";
echo "Uploads:    " . strtoupper($uploadDirStatus) . " (" . round($uploadDirSize / 1024 / 1024, 2) . " MB)\n";

// Verificar espacio disponible
$diskSpace = disk_free_space(__DIR__);
$diskTotal = disk_total_space(__DIR__);
$diskUsagePercent = round(((($diskTotal - $diskSpace) / $diskTotal) * 100), 2);
$diskStatus = $diskUsagePercent > 90 ? Health::CRITICAL : ($diskUsagePercent > 80 ? Health::WARNING : Health::HEALTHY);

echo "Espacio disponible: " . strtoupper($diskStatus) . " (" . $diskUsagePercent . "%)\n";
echo "Espacio libre:      " . round($diskSpace / 1024 / 1024 / 1024, 2) . " GB\n";

$checks['storage'] = [
    'status' => $diskStatus,
    'logs_mb' => round($logDirSize / 1024 / 1024, 2),
    'uploads_mb' => round($uploadDirSize / 1024 / 1024, 2),
    'disk_usage_percent' => $diskUsagePercent
];

// 4. Verificar archivos críticos
echo "\n⚙️  Archivos Críticos\n";
echo "─" . str_repeat("─", 40) . "\n";

$criticalFiles = [
    'Config' => __DIR__ . '/src/Config.php',
    'Logger' => __DIR__ . '/src/Logger.php',
    'API' => __DIR__ . '/public/api.php',
    'Database' => __DIR__ . '/src/Database.php'
];

$filesStatus = Health::HEALTHY;
foreach ($criticalFiles as $name => $path) {
    $exists = file_exists($path);
    $status = $exists ? '✓' : '✗';
    echo "$status $name\n";
    
    if (!$exists) {
        $filesStatus = Health::CRITICAL;
    }
}

$checks['files'] = [
    'status' => $filesStatus
];

// 5. Verificar permisos
echo "\n🔐 Permisos\n";
echo "─" . str_repeat("─", 40) . "\n";

$permissionsStatus = Health::HEALTHY;
$directorios = [
    'logs' => __DIR__ . '/logs',
    'uploads' => __DIR__ . '/public/uploads',
    'config' => __DIR__ . '/src'
];

foreach ($directorios as $name => $path) {
    if (is_dir($path)) {
        $writable = is_writable($path);
        $status = $writable ? '✓' : '✗';
        echo "$status $name es escribible\n";
        
        if (!$writable) {
            $permissionsStatus = Health::WARNING;
        }
    } else {
        echo "✗ $name - Directorio no existe\n";
        $permissionsStatus = Health::CRITICAL;
    }
}

$checks['permissions'] = [
    'status' => $permissionsStatus
];

// 6. Verificar logs
echo "\n📊 Estadísticas de Logs\n";
echo "─" . str_repeat("─", 40) . "\n";

$logsFile = __DIR__ . '/logs/application.log';
$logsErrors = 0;
$logsCriticals = 0;

if (file_exists($logsFile)) {
    $lines = file($logsFile);
    foreach ($lines as $line) {
        if (strpos($line, '"level":"ERROR"') !== false) {
            $logsErrors++;
        } elseif (strpos($line, '"level":"CRITICAL"') !== false) {
            $logsCriticals++;
        }
    }
}

$logsStatus = $logsCriticals > 10 ? Health::CRITICAL : ($logsErrors > 50 ? Health::WARNING : Health::HEALTHY);

echo "Errores:    " . $logsErrors . "\n";
echo "Críticos:   " . $logsCriticals . "\n";
echo "Estado:     " . strtoupper($logsStatus) . "\n";

$checks['logs'] = [
    'status' => $logsStatus,
    'errors' => $logsErrors,
    'criticals' => $logsCriticals
];

// 7. Estado general
echo "\n📈 Estado General\n";
echo "─" . str_repeat("─", 40) . "\n";

$criticalCount = 0;
$warningCount = 0;

foreach ($checks as $check) {
    if ($check['status'] === Health::CRITICAL) {
        $criticalCount++;
    } elseif ($check['status'] === Health::WARNING) {
        $warningCount++;
    }
}

$overallStatus = $criticalCount > 0 ? Health::CRITICAL : ($warningCount > 0 ? Health::WARNING : Health::HEALTHY);

echo "Estado:     " . strtoupper($overallStatus) . "\n";
echo "Críticos:   " . $criticalCount . "\n";
echo "Advertencias: " . $warningCount . "\n";

// Log en AdvancedLogger
try {
    $logger->info('Health check completado', [
        'status' => $overallStatus,
        'critical_count' => $criticalCount,
        'warning_count' => $warningCount,
        'timestamp' => $timestamp
    ]);
} catch (Exception $e) {
    // Silenciar errores de logging
}

// Guardar reporte en JSON
$report = [
    'timestamp' => $timestamp,
    'overall_status' => $overallStatus,
    'critical_count' => $criticalCount,
    'warning_count' => $warningCount,
    'checks' => $checks
];

$reportFile = __DIR__ . '/logs/health-check-' . date('Y-m-d') . '.json';
file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "\n✅ Reporte guardado: " . basename($reportFile) . "\n";

// Exit code para uso en scripts
$exitCode = ($overallStatus === Health::CRITICAL) ? 2 : ($overallStatus === Health::WARNING ? 1 : 0);
exit($exitCode);
?>
