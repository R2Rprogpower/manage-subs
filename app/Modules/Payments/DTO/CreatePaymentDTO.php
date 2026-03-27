<?php

declare(strict_types=1);

namespace App\Modules\Payments\DTO;

readonly class CreatePaymentDTO
{
    public function __construct(
        public int $subscriptionId,
        public string $provider,
        public ?string $providerPaymentId,
        public string $status,
        public int $amountMinor,
        public string $currency,
        public ?string $paidAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'subscription_id' => $this->subscriptionId,
            'provider' => $this->provider,
            'provider_payment_id' => $this->providerPaymentId,
            'status' => $this->status,
            'amount_minor' => $this->amountMinor,
            'currency' => strtoupper($this->currency),
            'paid_at' => $this->paidAt,
        ];
    }
}
