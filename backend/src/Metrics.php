<?php
namespace SpotMap;

/**
 * Metrics acumuladas en memoria del proceso.
 * Para entornos multi-worker se requeriría agregación externa.
 */
class Metrics
{
    private static array $counters = [
        'requests_total' => 0,
        'requests_spots_list' => 0,
        'requests_spots_show' => 0,
        'requests_spots_create' => 0,
        'requests_spots_delete' => 0,
        'requests_spots_upload' => 0,
        'requests_account_delete' => 0,
        'requests_account_export' => 0,
    ];

    private static int $durationCount = 0;
    private static float $durationSumMs = 0.0;
    private static float $durationMaxMs = 0.0;
    private static float $durationMinMs = 0.0;

    public static function inc(string $key, int $value = 1): void
    {
        if (!isset(self::$counters[$key])) {
            self::$counters[$key] = 0;
        }
        self::$counters[$key] += $value;
    }

    public static function all(): array
    {
        return array_merge(self::$counters, [
            'latency_count' => self::$durationCount,
            'latency_sum_ms' => round(self::$durationSumMs,2),
            'latency_avg_ms' => self::$durationCount ? round(self::$durationSumMs / self::$durationCount,2) : 0,
            'latency_max_ms' => round(self::$durationMaxMs,2),
            'latency_min_ms' => round(self::$durationMinMs,2),
        ]);
    }

    public static function recordDuration(float $ms): void
    {
        self::$durationCount++;
        self::$durationSumMs += $ms;
        if (self::$durationCount === 1) {
            self::$durationMinMs = $ms;
            self::$durationMaxMs = $ms;
        } else {
            if ($ms < self::$durationMinMs) self::$durationMinMs = $ms;
            if ($ms > self::$durationMaxMs) self::$durationMaxMs = $ms;
        }
    }
}
