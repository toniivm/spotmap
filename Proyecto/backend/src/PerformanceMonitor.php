<?php
/**
 * ⚠️ SPOTMAP - PERFORMANCE MONITORING
 * Copyright (c) 2025 Antonio Valero. Todos los derechos reservados.
 */

namespace SpotMap;

class PerformanceMonitor {
    private static $startTime;
    private static $startMemory;
    private static $markers = [];

    /**
     * Start monitoring
     */
    public static function start() {
        self::$startTime = microtime(true);
        self::$startMemory = memory_get_usage(true);
    }

    /**
     * Mark a point in time
     */
    public static function mark($label) {
        self::$markers[$label] = [
            'time' => microtime(true),
            'memory' => memory_get_usage(true)
        ];
    }

    /**
     * Measure time between markers
     */
    public static function measure($label1, $label2) {
        if (!isset(self::$markers[$label1]) || !isset(self::$markers[$label2])) {
            return null;
        }

        $timeDiff = self::$markers[$label2]['time'] - self::$markers[$label1]['time'];
        $memoryDiff = self::$markers[$label2]['memory'] - self::$markers[$label1]['memory'];

        return [
            'time_ms' => round($timeDiff * 1000, 2),
            'memory_kb' => round($memoryDiff / 1024, 2)
        ];
    }

    /**
     * Get total execution time
     */
    public static function getTotalTime() {
        return (microtime(true) - self::$startTime) * 1000; // Convert to ms
    }

    /**
     * Get memory usage
     */
    public static function getMemoryUsage() {
        return memory_get_usage(true);
    }

    /**
     * Get peak memory usage
     */
    public static function getPeakMemory() {
        return memory_get_peak_usage(true);
    }

    /**
     * Get memory increase since start
     */
    public static function getMemoryIncrease() {
        return memory_get_usage(true) - self::$startMemory;
    }

    /**
     * Get all markers
     */
    public static function getMarkers() {
        return self::$markers;
    }

    /**
     * Get performance summary
     */
    public static function getSummary() {
        return [
            'total_time_ms' => round(self::getTotalTime(), 2),
            'memory_usage_mb' => round(self::getMemoryUsage() / 1048576, 2),
            'peak_memory_mb' => round(self::getPeakMemory() / 1048576, 2),
            'memory_increase_kb' => round(self::getMemoryIncrease() / 1024, 2),
            'markers' => self::$markers
        ];
    }
}
