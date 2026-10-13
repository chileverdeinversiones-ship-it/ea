<?php

declare(strict_types=1);

namespace SafeModern\Domain\Credit;

use InvalidArgumentException;

/**
 * Aplica el monto recibido en el orden preliminar:
 * mora exigible -> interés pendiente -> capital -> excedente.
 *
 * No determina cuánto interés/mora se ha devengado, no actualiza la base
 * de datos y no reemplaza la política formal aprobada por la cooperativa.
 */
final class PaymentAllocator
{
    public function allocate(
        string $received,
        string $lateFeeDue,
        string $interestDue,
        string $principalOutstanding,
    ): PaymentAllocation {
        $receivedCents = self::toCents($received, 'received');
        $lateCents = self::toCents($lateFeeDue, 'lateFeeDue');
        $interestCents = self::toCents($interestDue, 'interestDue');
        $principalCents = self::toCents($principalOutstanding, 'principalOutstanding');

        $remaining = $receivedCents;
        $appliedLate = min($remaining, $lateCents);
        $remaining -= $appliedLate;

        $appliedInterest = min($remaining, $interestCents);
        $remaining -= $appliedInterest;

        $appliedPrincipal = min($remaining, $principalCents);
        $remaining -= $appliedPrincipal;

        return new PaymentAllocation(
            receivedCents: $receivedCents,
            lateFeeCents: $appliedLate,
            interestCents: $appliedInterest,
            principalCents: $appliedPrincipal,
            excessCents: $remaining,
            remainingLateFeeCents: $lateCents - $appliedLate,
            remainingInterestCents: $interestCents - $appliedInterest,
            remainingPrincipalCents: $principalCents - $appliedPrincipal,
        );
    }

    /**
     * Convierte una cantidad decimal escrita con punto y hasta dos decimales
     * en centavos enteros. No acepta separadores de miles ni notación científica.
     */
    public static function toCents(string $amount, string $field = 'amount'): int
    {
        $amount = trim($amount);

        if (!preg_match('/^(0|[1-9][0-9]{0,12})(?:\.([0-9]{1,2}))?$/D', $amount, $matches)) {
            throw new InvalidArgumentException(
                sprintf('El importe "%s" no es válido para el campo %s.', $amount, $field)
            );
        }

        $whole = (int) $matches[1];
        $fraction = str_pad($matches[2] ?? '', 2, '0');
        $cents = ($whole * 100) + (int) $fraction;

        if ($cents < 0) {
            throw new InvalidArgumentException('Los importes no pueden ser negativos.');
        }

        return $cents;
    }

    public static function formatCents(int $cents): string
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Los centavos no pueden ser negativos.');
        }

        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
