<?php

declare(strict_types=1);

namespace SafeModern\Tests\Unit\Domain\Credit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SafeModern\Domain\Credit\DailyInterestCalculator;

final class DailyInterestCalculatorTest extends TestCase
{
    private DailyInterestCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new DailyInterestCalculator();
    }

    public function testAccruesAnnualInterestOnOutstandingPrincipal(): void
    {
        // Q10,000.00 x 12% x 30/360 = Q100.00
        self::assertSame(10000, $this->calculator->accrue(1_000_000, 1200, 30, 360));
    }

    public function testUsesActualSelectedDayBasis(): void
    {
        // Q10,000 x 12% x 30/365, redondeado a centavos = Q98.63
        self::assertSame(9863, $this->calculator->accrue(1_000_000, 1200, 30, 365));
    }

    public function testZeroPrincipalRateOrDaysProducesZero(): void
    {
        self::assertSame(0, $this->calculator->accrue(0, 1200, 30, 365));
        self::assertSame(0, $this->calculator->accrue(1_000_000, 0, 30, 365));
        self::assertSame(0, $this->calculator->accrue(1_000_000, 1200, 0, 365));
    }

    public function testRejectsUnsupportedDayBasis(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->accrue(1_000_000, 1200, 30, 3650);
    }

    public function testRejectsInvalidRate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->accrue(1_000_000, 10001, 30, 365);
    }
}
