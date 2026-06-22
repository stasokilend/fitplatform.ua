<?php
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    public function testPasswordHashCanBeVerified(): void
    {
        $hash = password_hash('secret-password', PASSWORD_DEFAULT);
        $this->assertTrue(password_verify('secret-password', $hash));
    }
}
