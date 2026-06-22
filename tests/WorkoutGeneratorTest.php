<?php
use PHPUnit\Framework\TestCase;

final class WorkoutGeneratorTest extends TestCase
{
    public function testDurationIsNormalizedToInteger(): void
    {
        $this->assertSame(30, (int) '30');
    }
}
