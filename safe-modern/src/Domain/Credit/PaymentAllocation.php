<?php

declare(strict_types=1);

namespace SafeModern\Domain\Credit;

/**
 * Distribución de un pago recibido, sin persistencia ni efectos laterales.
 *
 * Todos los importes se expresan en centavos enteros para evitar cálculos
 * monetarios con float. El cálculo de intereses y mora ocurre antes de esta
 * clase y debe venir de un motor financiero probado.
 */
final readonly class PaymentAllocation
{
    public function __construct(
        public int $receivedCents,
        public int $lateFeeCents,
        public int $interestCents,
        public int $principalCents,
        public int $excessCents,
        public int $remainingLateFeeCents,
        public int $remainingInterestCents,
        public int $remainingPrincipalCents,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'received_cents' => $this->receivedCents,
            'late_fee_cents' => $this->lateFeeCents,
            'interest_cents' => $this->interestCents,
            'principal_cents' => $this->principalCents,
            'excess_cents' => $this->excessCents,
            'remaining_late_fee_cents' => $this->remainingLateFeeCents,
            'remaining_interest_cents' => $this->remainingInterestCents,
            'remaining_principal_cents' => $this->remainingPrincipalCents,
        ];
    }
}
