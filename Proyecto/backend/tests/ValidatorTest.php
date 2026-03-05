<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/Validator.php';

class ValidatorTest extends TestCase
{
    public function testRequiredAndStringValidation()
    {
        $v = new SpotMap\Validator();
        $v->required('', 'title')->string('ab', 'title', 3, 10);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('title', $v->errors());
    }

    public function testNumericAndCoordinates()
    {
        $v = new SpotMap\Validator();
        $v->numeric('abc', 'rating')
          ->latitude(200, 'lat')
          ->longitude(-200, 'lng');
        $this->assertTrue($v->fails());
        $errors = $v->errors();
        $this->assertArrayHasKey('rating', $errors);
        $this->assertArrayHasKey('lat', $errors);
        $this->assertArrayHasKey('lng', $errors);
    }

    public function testFileValidation()
    {
        $v = new SpotMap\Validator();
        $file = [
            'type' => 'image/png',
            'size' => 5 * 1024 * 1024
        ];
        $v->mimeType($file, 'image', ['image/jpeg'])
          ->fileSize($file, 'image', 1024 * 1024);
        $this->assertTrue($v->fails());
    }
}
