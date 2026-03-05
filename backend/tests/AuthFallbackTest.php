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
    private function makeJwt(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $h = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $p = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        return $h . '.' . $p . '.signature';
    }

    public function testFetchUserWithFallbackValidJwt(): void
    {
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
