<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/Config.php';

class ConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('DEBUG');
        unset($_ENV['DEBUG']);

        $ref = new ReflectionClass(SpotMap\Config::class);
        $loaded = $ref->getProperty('loaded');
        $loaded->setAccessible(true);
        $loaded->setValue(null, false);
    }

    public function testGetAllMasksPassword()
    {
        $ref = new ReflectionClass(SpotMap\Config::class);
        $prop = $ref->getProperty('config');
        $prop->setAccessible(true);
        $config = $prop->getValue();
        $config['DB_PASSWORD'] = 'secret';
        $prop->setValue(null, $config);

        $all = SpotMap\Config::getAll();
        $this->assertSame('***', $all['DB_PASSWORD']);
    }

    public function testIsDebugParsesFalseString(): void
    {
        putenv('DEBUG=false');
        $_ENV['DEBUG'] = 'false';

        $ref = new ReflectionClass(SpotMap\Config::class);
        $loaded = $ref->getProperty('loaded');
        $loaded->setAccessible(true);
        $loaded->setValue(null, false);

        $this->assertFalse(SpotMap\Config::isDebug());
    }

    public function testIsDebugParsesTrueString(): void
    {
        putenv('DEBUG=true');
        $_ENV['DEBUG'] = 'true';

        $ref = new ReflectionClass(SpotMap\Config::class);
        $loaded = $ref->getProperty('loaded');
        $loaded->setAccessible(true);
        $loaded->setValue(null, false);

        $this->assertTrue(SpotMap\Config::isDebug());
    }
}
