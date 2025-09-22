<?php

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    public function testGetRideCreateFeeReturnsInt(): void
    {
        $fee = getRideCreateFee();
        $this->assertIsInt($fee);
    }

    public function testGetRideCreateFeeDefaultIsNonNegative(): void
    {
        $fee = getRideCreateFee();
        $this->assertGreaterThanOrEqual(0, $fee);
    }
}
