<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/Security.php';

class SecuritySanitizeTest extends TestCase
{
    public function testSanitizeInputBlocksSqlPatterns()
    {
        $this->expectException(Exception::class);
        SpotMap\Security::sanitizeInput('UNION SELECT * FROM users', 'string');
    }

    public function testSanitizeInputRemovesTags()
    {
        $clean = SpotMap\Security::sanitizeInput('<b>Hello</b>', 'string');
        $this->assertSame('Hello', $clean);
    }
}
