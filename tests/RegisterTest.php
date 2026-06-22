<?php
use PHPUnit\Framework\TestCase;

final class RegisterTest extends TestCase
{
    public function testEmailValidationAcceptsValidEmail(): void
    {
        $this->assertSame('user@example.com', filter_var('user@example.com', FILTER_VALIDATE_EMAIL));
    }
}
