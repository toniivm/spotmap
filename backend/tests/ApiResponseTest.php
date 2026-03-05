<?php
use PHPUnit\Framework\TestCase;
use SpotMap\ApiResponse;

class ApiResponseTest extends TestCase
{
    public function testSuccessStructure()
    {
        ob_start();
        ApiResponse::success(['a'=>1]);
        $out = ob_get_clean();
        $json = json_decode($out, true);
        $this->assertArrayHasKey('success', $json);
        $this->assertTrue($json['success']);
        $this->assertEquals(1, $json['data']['a']);
    }

    public function testErrorStructure()
    {
        ob_start();
        ApiResponse::error('Bad', 400);
        $out = ob_get_clean();
        $json = json_decode($out, true);
        $this->assertFalse($json['success']);
        $this->assertEquals('Bad', $json['message']);
        $this->assertEquals(400, $json['status']);
    }
}
