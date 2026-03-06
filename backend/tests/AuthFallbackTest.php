<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/ApiResponse.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/DatabaseAdapter.php';
require_once __DIR__ . '/../src/Constants.php';
require_once __DIR__ . '/../src/Auth.php';

class AuthFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setEnv('SUPABASE_URL', '');
        $this->setEnv('SUPABASE_SERVICE_KEY', '');
        $this->setEnv('SUPABASE_ANON_KEY', '');
        $this->setEnv('SUPABASE_JWT_SECRET', '');
        $this->setEnv('ALLOW_INSECURE_JWT_FALLBACK', 'false');
        $this->setEnv('ENV', 'development');
        $this->resetConfigLoaded();
    }

    private function resetConfigLoaded(): void
    {
        $ref = new ReflectionClass(SpotMap\Config::class);
        $loaded = $ref->getProperty('loaded');
        $loaded->setAccessible(true);
        $loaded->setValue(null, false);
    }

    private function setConfigValues(array $values): void
    {
        SpotMap\Config::load();

        $ref = new ReflectionClass(SpotMap\Config::class);
        $configProp = $ref->getProperty('config');
        $configProp->setAccessible(true);
        $config = $configProp->getValue();

        foreach ($values as $key => $value) {
            $config[$key] = $value;
        }

        $configProp->setValue(null, $config);

        $loaded = $ref->getProperty('loaded');
        $loaded->setAccessible(true);
        $loaded->setValue(null, true);
    }

    private function setEnv(string $key, string $value): void
    {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function makeJwt(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $h = $this->base64UrlEncode((string)json_encode($header));
        $p = $this->base64UrlEncode((string)json_encode($payload));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $h . '.' . $p, 'test-secret', true));
        return $h . '.' . $p . '.' . $signature;
    }

    public function testFetchUserRequiresSecureFallbackConfiguration(): void
    {
        $token = $this->makeJwt([
            'sub' => 'user-123',
            'email' => 'test@example.com',
            'exp' => time() + 3600,
            'user_metadata' => ['name' => 'Tester']
        ]);

        $this->assertNull(SpotMap\Auth::fetchUser($token));
    }

    public function testFetchUserWithFallbackValidJwtAndSecret(): void
    {
        $this->setConfigValues([
            'SUPABASE_JWT_SECRET' => 'test-secret',
            'ALLOW_INSECURE_JWT_FALLBACK' => 'false',
            'ENV' => 'development',
        ]);

        $token = $this->makeJwt([
            'sub' => 'user-123',
            'email' => 'test@example.com',
            'exp' => time() + 3600,
            'user_metadata' => ['name' => 'Tester']
        ]);

        $user = SpotMap\Auth::fetchUser($token);

        $this->assertIsArray($user);
        $this->assertSame('user-123', $user['id']);
        $this->assertSame('test@example.com', $user['email']);
        $this->assertSame('authenticated', $user['role']);
    }

    public function testFetchUserWithInsecureFallbackAllowedInDevelopment(): void
    {
        $this->setConfigValues([
            'SUPABASE_JWT_SECRET' => '',
            'ALLOW_INSECURE_JWT_FALLBACK' => 'true',
            'ENV' => 'development',
        ]);

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'sub' => 'dev-user-1',
            'email' => 'dev@example.com',
            'exp' => time() + 3600,
        ];
        $h = $this->base64UrlEncode((string)json_encode($header));
        $p = $this->base64UrlEncode((string)json_encode($payload));
        $token = $h . '.' . $p . '.dummy-signature';

        $user = SpotMap\Auth::fetchUser($token);
        $this->assertIsArray($user);
        $this->assertSame('dev-user-1', $user['id']);
    }

    public function testFetchUserWithExpiredJwtReturnsNull(): void
    {
        $token = $this->makeJwt([
            'sub' => 'user-123',
            'exp' => time() - 10
        ]);

        $this->assertNull(SpotMap\Auth::fetchUser($token));
    }

    public function testFetchUserWithInvalidFormatReturnsNull(): void
    {
        $this->assertNull(SpotMap\Auth::fetchUser('not-a-jwt'));
    }
}
