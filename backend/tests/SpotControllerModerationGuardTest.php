<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/ApiResponse.php';
require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/Constants.php';
require_once __DIR__ . '/../src/Controllers/SpotController.php';

class SpotControllerModerationGuardTest extends TestCase
{
    public function testModerationGuardRejectsNonPendingStatus(): void
    {
        $this->assertFalse(SpotMap\Controllers\SpotController::canModerateStatusTransition('approved'));
        $this->assertFalse(SpotMap\Controllers\SpotController::canModerateStatusTransition('rejected'));
        $this->assertFalse(SpotMap\Controllers\SpotController::canModerateStatusTransition('unknown'));
    }

    public function testModerationGuardAllowsPendingStatus(): void
    {
        $this->assertTrue(SpotMap\Controllers\SpotController::canModerateStatusTransition('pending'));
    }
}
