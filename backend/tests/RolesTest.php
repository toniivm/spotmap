<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/ApiResponse.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/DatabaseAdapter.php';
require_once __DIR__ . '/../src/Roles.php';

class RolesTest extends TestCase
{
    protected function setUp(): void
    {
        SpotMap\Roles::clearRoleCache();

        putenv('ROLE_CACHE_ENABLED=true');
        $_ENV['ROLE_CACHE_ENABLED'] = 'true';

        $configRef = new ReflectionClass(SpotMap\Config::class);
        $loadedProp = $configRef->getProperty('loaded');
        $loadedProp->setAccessible(true);
        $loadedProp->setValue(null, false);

        $configProp = $configRef->getProperty('config');
        $configProp->setAccessible(true);
        $configProp->setValue(null, []);

        SpotMap\Config::load();
    }

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

    public function testAtLeastSupportsUserArray(): void
    {
        $user = ['role' => 'moderator'];
        $this->assertTrue(SpotMap\Roles::atLeast($user, 'user'));
        $this->assertFalse(SpotMap\Roles::atLeast($user, 'admin'));
    }

    public function testClearRoleCacheForSpecificUser(): void
    {
        $rolesRef = new ReflectionClass(SpotMap\Roles::class);
        $cacheProp = $rolesRef->getProperty('roleCache');
        $cacheProp->setAccessible(true);
        $cacheProp->setValue(null, [
            'user-1' => ['role' => 'moderator', 'expires_at' => time() + 120],
            'user-2' => ['role' => 'admin', 'expires_at' => time() + 120],
        ]);

        SpotMap\Roles::clearRoleCache('user-1');
        $cache = $cacheProp->getValue();

        $this->assertArrayNotHasKey('user-1', $cache);
        $this->assertArrayHasKey('user-2', $cache);
    }

    public function testExpiredCacheEntryIsIgnoredAndRemoved(): void
    {
        $rolesRef = new ReflectionClass(SpotMap\Roles::class);
        $cacheProp = $rolesRef->getProperty('roleCache');
        $cacheProp->setAccessible(true);
        $cacheProp->setValue(null, [
            'expired-user' => ['role' => 'moderator', 'expires_at' => time() - 1],
        ]);

        $resolvedRole = SpotMap\Roles::getUserRole(['id' => 'expired-user']);
        $cacheAfter = $cacheProp->getValue();

        $this->assertSame('user', $resolvedRole);
        $this->assertArrayNotHasKey('expired-user', $cacheAfter);
    }

    public function testCacheEntryIsIgnoredWhenRoleCacheDisabled(): void
    {
        putenv('ROLE_CACHE_ENABLED=false');
        $_ENV['ROLE_CACHE_ENABLED'] = 'false';

        $configRef = new ReflectionClass(SpotMap\Config::class);
        $loadedProp = $configRef->getProperty('loaded');
        $loadedProp->setAccessible(true);
        $loadedProp->setValue(null, false);

        $configProp = $configRef->getProperty('config');
        $configProp->setAccessible(true);
        $configProp->setValue(null, []);

        SpotMap\Config::load();

        // Even if cache has a privileged role, disabled cache must ignore it.
        $rolesRef = new ReflectionClass(SpotMap\Roles::class);
        $cacheProp = $rolesRef->getProperty('roleCache');
        $cacheProp->setAccessible(true);
        $cacheProp->setValue(null, [
            'user-without-cache' => ['role' => 'admin', 'expires_at' => time() + 3600],
        ]);

        $role = SpotMap\Roles::getUserRole(['id' => 'user-without-cache']);
        $cacheAfter = $cacheProp->getValue();

        $this->assertSame('user', $role);
        $this->assertArrayHasKey('user-without-cache', $cacheAfter);
    }
}
