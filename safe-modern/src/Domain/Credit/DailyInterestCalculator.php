<?php

declare(strict_types=1);

namespace SafeModern\Domain\Credit;

use InvalidArgumentException;

/**
 * Devengo de interés ordinario simple sobre saldo de capital.
 *
 * Importes en centavos enteros; tasa anual expresada en puntos básicos:
 * 1250 equivale a 12.50 %. No decide si el día inicial/final es inclusivo;
 * el llamador debe entregar días devengados conforme a la política vigente.
 */
final class DailyInterestCalculator
{
    public function accrue(
        int $principalCents,
        int $annualRateBasisPoints,
        int $days,
        int $dayCountBasis,
    ): int {
        if ($principalCents < 0 || $principalCents > 900_000_000_000) {
            throw new InvalidArgumentException('El saldo de capital está fuera del rango permitido.');
        }
        if ($annualRateBasisPoints < 0 || $annualRateBasisPoints > 10_000) {
            throw new InvalidArgumentException('La tasa debe estar entre 0 % y 100 % anual.');
        }
        if ($days < 0 || $days > 366) {
            throw new InvalidArgumentException('Los días devengados deben estar entre 0 y 366.');
        }
        if (!in_array($dayCountBasis, [360, 365, 366], true)) {
            throw new InvalidArgumentException('La base de días debe ser 360, 365 o 366.');
        }

        if ($principalCents === 0 || $annualRateBasisPoints === 0 || $days === 0) {
            return 0;
        }

        // Redondeo comercial a centavo más cercano, mitad hacia arriba.
        $numerator = $principalCents * $annualRateBasisPoints * $days;
        $denominator = 10_000 * $dayCountBasis;

        return intdiv($numerator + intdiv($denominator, 2), $denominator);
    }
}
