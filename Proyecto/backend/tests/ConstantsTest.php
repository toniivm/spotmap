<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Constants.php';

class ConstantsTest extends TestCase
{
    public function testCategoryValidation(): void
    {
        $this->assertTrue(SpotMap\Constants::isValidCategory('naturaleza'));
        $this->assertFalse(SpotMap\Constants::isValidCategory('inexistente'));
    }

    public function testStatusValidation(): void
    {
        $this->assertTrue(SpotMap\Constants::isValidStatus('pending'));
        $this->assertTrue(SpotMap\Constants::isValidStatus('approved'));
        $this->assertFalse(SpotMap\Constants::isValidStatus('hidden'));
    }

    public function testRoleValidationAndModeration(): void
    {
        $this->assertTrue(SpotMap\Constants::isValidRole('user'));
        $this->assertTrue(SpotMap\Constants::isValidRole('moderator'));
        $this->assertTrue(SpotMap\Constants::isValidRole('admin'));
        $this->assertFalse(SpotMap\Constants::isValidRole('superadmin'));

        $this->assertFalse(SpotMap\Constants::isModerator('user'));
        $this->assertTrue(SpotMap\Constants::isModerator('moderator'));
        $this->assertTrue(SpotMap\Constants::isModerator('admin'));
    }

    public function testCategoriesAndStatusStringHelpers(): void
    {
        $categories = SpotMap\Constants::getCategoriesString();
        $status = SpotMap\Constants::getStatusString();

        $this->assertIsString($categories);
        $this->assertIsString($status);
        $this->assertStringContainsString('naturaleza', $categories);
        $this->assertStringContainsString('pending', $status);
    }
}
