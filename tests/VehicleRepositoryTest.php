<?php

use PHPUnit\Framework\TestCase;
use App\Repository\VehicleRepository;

final class VehicleRepositoryTest extends TestCase
{
    /**
     * @dataProvider plateProvider
     */
    public function testNormalizePlate(string $input, string $expected): void
    {
        $this->assertSame($expected, VehicleRepository::normalizePlate($input));
    }

    public static function plateProvider(): array
    {
        return [
            ['aa-123-bb', 'AA123BB'],
            [' AA 123 BB ', 'AA123BB'],
            ['aa.123.bb', 'AA123BB'],
            ['  a a 1 2 3 b b  ', 'AA123BB'],
            ['', ''],
        ];
    }
}
