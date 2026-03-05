<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Validator.php';

class ValidatorAdvancedTest extends TestCase
{
    public function testInValidationRejectsInvalidEnum(): void
    {
        $validator = new SpotMap\Validator();
        $validator->in('invalid_category', 'category', ['playa', 'naturaleza', 'urbano']);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category', $validator->errors());
    }

    public function testArrayValidationConstraints(): void
    {
        $validator = new SpotMap\Validator();
        $validator->array(['tag1', 'tag2', str_repeat('x', 60)], 'tags', 2, 50);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertArrayHasKey('tags', $errors);
        $this->assertNotEmpty($errors['tags']);
    }

    public function testSanitizeAndCleanNestedData(): void
    {
        $validator = new SpotMap\Validator();

        $raw = [
            'title' => '  <script>alert(1)</script>  ',
            'tags' => ['  <b>safe</b>  ', ' normal '],
            'meta' => [
                'description' => '  <img src=x onerror=alert(1)>  '
            ]
        ];

        $clean = $validator->clean($raw);

        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $clean['title']);
        $this->assertSame('&lt;b&gt;safe&lt;/b&gt;', $clean['tags'][0]);
        $this->assertSame('normal', $clean['tags'][1]);
        $this->assertSame('&lt;img src=x onerror=alert(1)&gt;', $clean['meta']['description']);
    }

    public function testGetErrorsAliasMatchesErrorsMethod(): void
    {
        $validator = new SpotMap\Validator();
        $validator->required('', 'title');

        $this->assertSame($validator->errors(), $validator->getErrors());
    }
}
