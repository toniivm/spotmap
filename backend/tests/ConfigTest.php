<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/Config.php';

class ConfigTest extends TestCase
{
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
}
