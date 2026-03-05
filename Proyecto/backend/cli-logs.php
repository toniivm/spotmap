<?php
/**
 * ⚠️ CÓDIGO PROPIETARIO - SPOTMAP ⚠️
 * CLI Tool para gestionar logs y monitoreo
 * Copyright (c) 2025 Antonio Valero
 */
declare(strict_types=1);

require __DIR__ . '/src/Config.php';
require __DIR__ . '/src/Logger.php';

use SpotMap\Logger;

// Colores para terminal
class ConsoleColors {
    const RESET = "\033[0m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const MAGENTA = "\033[35m";
    const CYAN = "\033[36m";
    const WHITE = "\033[37m";
    const GRAY = "\033[90m";
    
    public static function color(string $text, string $color): string {
        return $color . $text . self::RESET;
    }
}

// Parser de argumentos
$command = $argv[1] ?? 'help';
$option = $argv[2] ?? null;
$value = $argv[3] ?? null;

// Cargar configuración
\SpotMap\Config::load();
$logger = Logger::getInstance();

echo ConsoleColors::color("╔════════════════════════════════════════════════╗\n", ConsoleColors::CYAN);
echo ConsoleColors::color("║      SpotMap - Herramienta CLI de Logs        ║\n", ConsoleColors::CYAN);
echo ConsoleColors::color("╚════════════════════════════════════════════════╝\n", ConsoleColors::CYAN);
echo "\n";

switch ($command) {
    // Ver últimos logs
    case 'tail':
        $limit = (int)($option ?? 20);
        echo ConsoleColors::color("📋 Últimos $limit logs:\n", ConsoleColors::BLUE);
        echo str_repeat("─", 80) . "\n";
        
        $logs = $logger->getLogs($limit);
        if (empty($logs)) {
            echo ConsoleColors::color("❌ No hay logs disponibles\n", ConsoleColors::YELLOW);
        } else {
            foreach ($logs as $log) {
                $levelColor = match($log['level'] ?? 'INFO') {
                    'ERROR' => ConsoleColors::RED,
                    'CRITICAL' => ConsoleColors::RED,
                    'SECURITY' => ConsoleColors::MAGENTA,
                    'WARNING' => ConsoleColors::YELLOW,
                    'INFO' => ConsoleColors::GREEN,
                    default => ConsoleColors::WHITE
                };
                
                $timestamp = ConsoleColors::color($log['timestamp'] ?? 'N/A', ConsoleColors::GRAY);
                $level = ConsoleColors::color(sprintf("[%-8s]", $log['level'] ?? 'INFO'), $levelColor);
                $message = $log['message'] ?? 'N/A';
                
                echo "$timestamp $level $message\n";
                
                if (!empty($log['context']) && is_array($log['context'])) {
                    $contextStr = json_encode($log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    $contextStr = str_replace("\n", "\n  ", $contextStr);
                    echo ConsoleColors::color("  └─ Context: ", ConsoleColors::GRAY) . "$contextStr\n";
                }
            }
        }
        break;

    // Filtrar por nivel
    case 'filter':
        if (!$option) {
            echo ConsoleColors::color("❌ Especifica nivel: debug, info, warning, error, critical, security\n", ConsoleColors::RED);
            exit(1);
        }
        
        $limit = (int)($value ?? 50);
        echo ConsoleColors::color("🔍 Filtrando por nivel: " . strtoupper($option) . " (últimos $limit)\n", ConsoleColors::BLUE);
        echo str_repeat("─", 80) . "\n";
        
        $logs = $logger->getLogs($limit, strtoupper($option));
        if (empty($logs)) {
            echo ConsoleColors::color("❌ No hay logs con ese nivel\n", ConsoleColors::YELLOW);
        } else {
            foreach ($logs as $log) {
                $timestamp = ConsoleColors::color($log['timestamp'] ?? 'N/A', ConsoleColors::GRAY);
                $message = $log['message'] ?? 'N/A';
                
                echo "$timestamp - $message\n";
                
                if (!empty($log['context'])) {
                    $context = json_encode($log['context'], JSON_UNESCAPED_SLASHES);
                    echo ConsoleColors::color("  └─ ", ConsoleColors::GRAY) . "$context\n";
                }
            }
        }
        break;

    // Ver alertas
    case 'alerts':
        $limit = (int)($option ?? 20);
        echo ConsoleColors::color("🚨 Últimas $limit alertas:\n", ConsoleColors::MAGENTA);
        echo str_repeat("─", 80) . "\n";
        
        $alerts = $logger->getAlerts($limit);
        if (empty($alerts)) {
            echo ConsoleColors::color("✅ No hay alertas\n", ConsoleColors::GREEN);
        } else {
            foreach ($alerts as $alert) {
                $timestamp = ConsoleColors::color($alert['timestamp'] ?? 'N/A', ConsoleColors::GRAY);
                $level = ConsoleColors::color(sprintf("[%-8s]", $alert['level'] ?? 'ALERT'), ConsoleColors::RED);
                $message = $alert['message'] ?? 'N/A';
                
                echo "$timestamp $level $message\n";
                
                if (!empty($alert['context'])) {
                    $context = json_encode($alert['context'], JSON_UNESCAPED_SLASHES);
                    echo ConsoleColors::color("  └─ ", ConsoleColors::GRAY) . "$context\n";
                }
            }
        }
        break;

    // Ver métricas
    case 'metrics':
        echo ConsoleColors::color("📊 Resumen de Métricas:\n", ConsoleColors::BLUE);
        echo str_repeat("─", 80) . "\n";
        
        $metrics = $logger->getMetricsSummary();
        if (empty($metrics)) {
            echo ConsoleColors::color("❌ No hay métricas disponibles\n", ConsoleColors::YELLOW);
        } else {
            foreach ($metrics as $key => $value) {
                $formattedKey = ucwords(str_replace('_', ' ', $key));
                
                if (is_array($value)) {
                    echo ConsoleColors::color("$formattedKey:\n", ConsoleColors::GREEN);
                    foreach ($value as $subKey => $subValue) {
                        echo "  • " . ucwords(str_replace('_', ' ', $subKey)) . ": " . json_encode($subValue) . "\n";
                    }
                } else {
                    echo "• $formattedKey: " . json_encode($value) . "\n";
                }
            }
        }
        break;

    // Estadísticas generales
    case 'stats':
        echo ConsoleColors::color("📈 Estadísticas de Logs:\n", ConsoleColors::BLUE);
        echo str_repeat("─", 80) . "\n";
        
        $allLogs = $logger->getLogs(1000);
        $levelCounts = ['DEBUG' => 0, 'INFO' => 0, 'WARNING' => 0, 'ERROR' => 0, 'CRITICAL' => 0, 'SECURITY' => 0];
        
        foreach ($allLogs as $log) {
            $level = $log['level'] ?? 'INFO';
            if (isset($levelCounts[$level])) {
                $levelCounts[$level]++;
            }
        }
        
        echo "\nConteo por nivel:\n";
        foreach ($levelCounts as $level => $count) {
            $color = match($level) {
                'ERROR' => ConsoleColors::RED,
                'CRITICAL' => ConsoleColors::RED,
                'SECURITY' => ConsoleColors::MAGENTA,
                'WARNING' => ConsoleColors::YELLOW,
                default => ConsoleColors::GREEN
            };
            echo "  " . ConsoleColors::color(sprintf("%-10s", $level), $color) . ": $count\n";
        }
        
        echo "\nTotal de logs: " . count($allLogs) . "\n";
        break;

    // Limpiar logs antiguos
    case 'clean':
        $days = (int)($option ?? 7);
        echo ConsoleColors::color("🧹 Limpiando logs más antiguos que $days días...\n", ConsoleColors::YELLOW);
        echo str_repeat("─", 80) . "\n";
        
        $logsDir = __DIR__ . '/logs';
        if (!is_dir($logsDir)) {
            echo ConsoleColors::color("❌ Directorio de logs no encontrado\n", ConsoleColors::RED);
            exit(1);
        }
        
        $deleted = 0;
        $files = glob("$logsDir/*.{log,json}", GLOB_BRACE);
        
        foreach ($files as $file) {
            $fileAge = (time() - filemtime($file)) / (24 * 3600); // Días
            if ($fileAge > $days) {
                if (unlink($file)) {
                    echo ConsoleColors::color("✓", ConsoleColors::GREEN) . " Eliminado: " . basename($file) . "\n";
                    $deleted++;
                }
            }
        }
        
        echo "\n✅ Limpieza completada: $deleted archivos eliminados\n";
        break;

    // Exportar logs
    case 'export':
        if (!$option) {
            echo ConsoleColors::color("❌ Especifica formato: json, csv\n", ConsoleColors::RED);
            exit(1);
        }
        
        $limit = (int)($value ?? 1000);
        $logs = $logger->getLogs($limit);
        $filename = __DIR__ . '/logs/export_' . date('Y-m-d_H-i-s') . '.' . $option;
        
        echo ConsoleColors::color("💾 Exportando $limit logs a $filename\n", ConsoleColors::BLUE);
        
        if ($option === 'json') {
            file_put_contents($filename, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } elseif ($option === 'csv') {
            $handle = fopen($filename, 'w');
            fputcsv($handle, ['timestamp', 'level', 'message', 'ip', 'user_id', 'context']);
            
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log['timestamp'] ?? '',
                    $log['level'] ?? '',
                    $log['message'] ?? '',
                    $log['ip'] ?? '',
                    $log['user_id'] ?? '',
                    json_encode($log['context'] ?? [])
                ]);
            }
            
            fclose($handle);
        } else {
            echo ConsoleColors::color("❌ Formato no soportado: " . $option . "\n", ConsoleColors::RED);
            exit(1);
        }
        
        echo ConsoleColors::color("✅ Exportación completada\n", ConsoleColors::GREEN);
        break;

    // Ver archivo de logs
    case 'view':
        $logFile = $option ? __DIR__ . "/logs/$option" : __DIR__ . '/logs/application.log';
        
        if (!file_exists($logFile)) {
            echo ConsoleColors::color("❌ Archivo no encontrado: $logFile\n", ConsoleColors::RED);
            exit(1);
        }
        
        $lines = (int)($value ?? 50);
        echo ConsoleColors::color("📄 Mostrando últimas $lines líneas de: " . basename($logFile) . "\n", ConsoleColors::BLUE);
        echo str_repeat("─", 80) . "\n";
        
        // Usar tail si está disponible
        if (function_exists('exec')) {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            if ($isWindows) {
                exec("powershell -Command \"Get-Content '$logFile' -Tail $lines\"", $output);
            } else {
                exec("tail -$lines '$logFile'", $output);
            }
            
            foreach ($output as $line) {
                echo $line . "\n";
            }
        } else {
            $content = file_get_contents($logFile);
            $lines_array = explode("\n", $content);
            $last_lines = array_slice($lines_array, -$lines);
            
            foreach ($last_lines as $line) {
                if (!empty($line)) {
                    echo $line . "\n";
                }
            }
        }
        break;

    // Mostrar archivos disponibles
    case 'files':
        echo ConsoleColors::color("📂 Archivos de logs disponibles:\n", ConsoleColors::BLUE);
        echo str_repeat("─", 80) . "\n";
        
        $logsDir = __DIR__ . '/logs';
        if (!is_dir($logsDir)) {
            echo ConsoleColors::color("❌ Directorio de logs no encontrado\n", ConsoleColors::YELLOW);
            break;
        }
        
        $files = scandir($logsDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = "$logsDir/$file";
                $size = filesize($filePath);
                $sizeStr = $size > 1024*1024 ? round($size / (1024*1024), 2) . ' MB' : round($size / 1024, 2) . ' KB';
                $modified = date('Y-m-d H:i:s', filemtime($filePath));
                
                echo "  • $file ($sizeStr) - Modificado: $modified\n";
            }
        }
        break;

    // Ayuda
    case 'help':
    default:
        echo ConsoleColors::color("Uso: php cli-logs.php <comando> [opción] [valor]\n\n", ConsoleColors::CYAN);
        
        echo ConsoleColors::color("Comandos disponibles:\n", ConsoleColors::GREEN);
        echo "  " . ConsoleColors::color("tail [limite]", ConsoleColors::YELLOW) . "           Ver últimos logs (default: 20)\n";
        echo "  " . ConsoleColors::color("filter <nivel> [limite]", ConsoleColors::YELLOW) . "   Filtrar por nivel de log\n";
        echo "  " . ConsoleColors::color("alerts [limite]", ConsoleColors::YELLOW) . "         Ver últimas alertas\n";
        echo "  " . ConsoleColors::color("metrics", ConsoleColors::YELLOW) . "               Ver resumen de métricas\n";
        echo "  " . ConsoleColors::color("stats", ConsoleColors::YELLOW) . "                 Ver estadísticas generales\n";
        echo "  " . ConsoleColors::color("clean [días]", ConsoleColors::YELLOW) . "          Limpiar logs antiguos\n";
        echo "  " . ConsoleColors::color("export <json|csv> [limite]", ConsoleColors::YELLOW) . " Exportar logs\n";
        echo "  " . ConsoleColors::color("view [archivo] [líneas]", ConsoleColors::YELLOW) . "  Ver archivo específico\n";
        echo "  " . ConsoleColors::color("files", ConsoleColors::YELLOW) . "                 Listar archivos de logs\n";
        echo "  " . ConsoleColors::color("help", ConsoleColors::YELLOW) . "                  Esta ayuda\n";
        
        echo "\n" . ConsoleColors::color("Niveles disponibles:\n", ConsoleColors::GREEN);
        echo "  debug, info, warning, error, critical, security\n";
        
        echo "\n" . ConsoleColors::color("Ejemplos:\n", ConsoleColors::CYAN);
        echo "  php cli-logs.php tail 50\n";
        echo "  php cli-logs.php filter error 100\n";
        echo "  php cli-logs.php alerts 10\n";
        echo "  php cli-logs.php clean 30\n";
        echo "  php cli-logs.php export json 500\n";
        echo "  php cli-logs.php view metrics.json\n";
        break;
}

echo "\n";
?>
