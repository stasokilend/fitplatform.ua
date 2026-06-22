<?php
use PHPUnit\Framework\TestCase;

final class RolesTest extends TestCase
{
    public function testKnownRoles(): void
    {
        $this->assertContains('trainer', ['user', 'trainer', 'admin']);
    }
}
