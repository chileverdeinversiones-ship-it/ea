<?php

declare(strict_types=1);

namespace SafeModern\Tests\Unit\Domain\Credit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SafeModern\Domain\Credit\PaymentAllocator;

final class PaymentAllocatorTest extends TestCase
{
    private PaymentAllocator $allocator;

    protected function setUp(): void
    {
        $this->allocator = new PaymentAllocator();
    }

    public function testAllocatesMoraThenInterestThenPrincipal(): void
    {
        $result = $this->allocator->allocate('500.00', '75.00', '125.00', '3200.00');

        self::assertSame(50000, $result->receivedCents);
        self::assertSame(7500, $result->lateFeeCents);
        self::assertSame(12500, $result->interestCents);
        self::assertSame(30000, $result->principalCents);
        self::assertSame(0, $result->excessCents);
        self::assertSame(0, $result->remainingLateFeeCents);
        self::assertSame(0, $result->remainingInterestCents);
        self::assertSame(290000, $result->remainingPrincipalCents);
    }

    public function testPartialPaymentDoesNotPretendToPayWholeLateFee(): void
    {
        $result = $this->allocator->allocate('20.00', '75.00', '125.00', '3200.00');

        self::assertSame(2000, $result->lateFeeCents);
        self::assertSame(0, $result->interestCents);
        self::assertSame(0, $result->principalCents);
        self::assertSame(5500, $result->remainingLateFeeCents);
        self::assertSame(12500, $result->remainingInterestCents);
    }

    public function testPaymentCanReachCapitalAfterPayingDueAmounts(): void
    {
        $result = $this->allocator->allocate('250.00', '75.00', '125.00', '3200.00');

        self::assertSame(7500, $result->lateFeeCents);
        self::assertSame(12500, $result->interestCents);
        self::assertSame(5000, $result->principalCents);
        self::assertSame(315000, $result->remainingPrincipalCents);
    }

    public function testExcessIsReportedRatherThanSilentlyAssigned(): void
    {
        $result = $this->allocator->allocate('350.00', '75.00', '125.00', '100.00');

        self::assertSame(10000, $result->principalCents);
        self::assertSame(5000, $result->excessCents);
        self::assertSame(0, $result->remainingPrincipalCents);
    }

    public function testRejectsNegativeAndMalformedAmounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->allocator->allocate('-1.00', '0.00', '0.00', '1.00');
    }

    public function testRejectsMoreThanTwoDecimalPlaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->allocator->allocate('10.999', '0.00', '0.00', '1.00');
    }

    public function testAllAllocationsSumToAmountReceived(): void
    {
        $result = $this->allocator->allocate('0.07', '0.03', '0.02', '1.00');

        self::assertSame(
            $result->receivedCents,
            $result->lateFeeCents + $result->interestCents + $result->principalCents + $result->excessCents
        );
    }
}
