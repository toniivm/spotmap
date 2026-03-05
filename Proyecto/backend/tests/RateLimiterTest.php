<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/RateLimiter.php';

class RateLimiterTest extends TestCase
{
    protected function setUp(): void
    {
        $configRef = new ReflectionClass(SpotMap\Config::class);
        $configProp = $configRef->getProperty('config');
        $configProp->setAccessible(true);
        $config = $configProp->getValue();
        $config['RATE_LIMIT_ENABLED'] = true;
        $config['RATE_LIMIT_REQUESTS'] = 2;
        $config['RATE_LIMIT_WINDOW'] = 60;
        $configProp->setValue(null, $config);

        $rateRef = new ReflectionClass(SpotMap\RateLimiter::class);
        $logProp = $rateRef->getProperty('requestLog');
        $logProp->setAccessible(true);
        $logProp->setValue(null, []);
    }

    public function testRateLimitBlocksAfterMax()
    {
        $id = 'unit-test-' . uniqid();
        $this->assertTrue(SpotMap\RateLimiter::check($id));
        $this->assertTrue(SpotMap\RateLimiter::check($id));
        $this->assertFalse(SpotMap\RateLimiter::check($id));
        $this->assertSame(0, SpotMap\RateLimiter::getRemaining($id));
    }
}
