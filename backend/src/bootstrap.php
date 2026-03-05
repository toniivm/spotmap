<?php
declare(strict_types=1);

use SpotMap\Database;
use SpotMap\Cache;
use SpotMap\DatabaseAdapter;

$dbHost = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_DATABASE') ?: 'spotmap';
$dbUser = getenv('DB_USERNAME') ?: 'spotmap';
$dbPass = getenv('DB_PASSWORD') ?: 'spotmappw';
$dbPort = getenv('DB_PORT') ?: '3306';

Database::init();

// Warm cache avanzado (página 1 + popularidad + schedule variantes) si producción
if (getenv('ENV') === 'production') {
    try {
        $base = DatabaseAdapter::getAllSpots(50, 0);
        if (!isset($base['error'])) {
            Cache::set('spots_1_50', $base, 180);
        }
        // Popular spots (popularity flag)
        $popular = DatabaseAdapter::getAllSpots(50, 0, ['popularity' => true]);
        if (!isset($popular['error'])) {
            Cache::set('spots_1_50_popularity', $popular, 180);
        }
        // Common schedules (sunrise, golden_hour, night)
        foreach (['sunrise','golden_hour','night'] as $sched) {
            $scheduled = DatabaseAdapter::getAllSpots(25, 0, ['schedule' => $sched]);
            if (!isset($scheduled['error']) && !empty($scheduled['spots'])) {
                Cache::set('spots_1_25_schedule_' . $sched, $scheduled, 180);
            }
        }
    } catch (\Throwable $e) { /* silent */ }
}
