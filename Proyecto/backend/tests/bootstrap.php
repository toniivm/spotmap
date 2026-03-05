<?php
define('SPOTMAP_TESTING', true);
require __DIR__ . '/../src/Config.php';
\SpotMap\Config::load();
require __DIR__ . '/../src/ApiResponse.php';
require __DIR__ . '/../src/Cache.php';
