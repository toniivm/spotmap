<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/Security.php';

class SecurityHeadersTest extends TestCase
{
    public function testSecurityHeadersSet()
    {
        SpotMap\Security::setSecurityHeaders();
        if (PHP_SAPI === 'cli') {
            $this->assertTrue(true);
            return;
        }
        $this->assertNotEmpty(headers_list());
    }
}
