<?php
use PHPUnit\Framework\TestCase;
use SpotMap\Cache;

class CacheTest extends TestCase
{
    public function testSetAndGet()
    {
        Cache::set('unit_key', ['x'=>5], 2);
        $v = Cache::get('unit_key');
        $this->assertEquals(5, $v['x']);
    }

    public function testExpire()
    {
        Cache::set('unit_expire', 'abc', 1);
        sleep(2);
        $v = Cache::get('unit_expire');
        $this->assertNull($v);
    }
}
