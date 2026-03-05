<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/ApiResponse.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/DatabaseAdapter.php';
require_once __DIR__ . '/../src/Roles.php';

class RolesTest extends TestCase
{
    public function testGetUserRoleUsesDirectRole(): void
    {
        $user = ['role' => 'moderator'];
        $this->assertSame('moderator', SpotMap\Roles::getUserRole($user));
    }

    public function testGetUserRoleUsesAppMetadataRole(): void
    {
        $user = ['app_metadata' => ['role' => 'admin']];
        $this->assertSame('admin', SpotMap\Roles::getUserRole($user));
    }

    public function testGetUserRoleUsesUserMetadataRole(): void
    {
        $user = ['user_metadata' => ['role' => 'moderator']];
        $this->assertSame('moderator', SpotMap\Roles::getUserRole($user));
    }

    public function testGetUserRoleFallbackIsUserWhenUnknown(): void
    {
        $user = ['app_metadata' => ['role' => 'unknown-role']];
        $this->assertSame('user', SpotMap\Roles::getUserRole($user));
    }

    public function testAtLeastHierarchy(): void
    {
        $this->assertTrue(SpotMap\Roles::atLeast('admin', 'moderator'));
        $this->assertTrue(SpotMap\Roles::atLeast('moderator', 'user'));
        $this->assertFalse(SpotMap\Roles::atLeast('user', 'moderator'));
    }
}
