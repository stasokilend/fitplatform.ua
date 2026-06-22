<?php
use PHPUnit\Framework\TestCase;

final class AdminRoleSwitchTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testAdminKeepsActualRoleWhileUsingTrainerTestRole(): void
    {
        $_SESSION['user_role'] = 'admin';
        $_SESSION['admin_test_role'] = 'trainer';

        $this->assertSame('admin', getActualUserRole());
        $this->assertTrue(isAdminRole());
        $this->assertSame('trainer', getEffectiveUserRole());
    }

    public function testAdminDefaultsToUserTestRole(): void
    {
        $_SESSION['user_role'] = 'admin';

        $this->assertSame('user', getEffectiveUserRole());
    }
}
