<?php

declare(strict_types=1);

namespace App\Modules\Plans\DTO;

readonly class CreatePlanDTO
{
    public function __construct(
        public string $code,
        public string $name,
        public int $priceMinor,
        public string $currency,
        public ?int $durationDays,
        public bool $isActive,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'price_minor' => $this->priceMinor,
            'currency' => strtoupper($this->currency),
            'duration_days' => $this->durationDays,
            'is_active' => $this->isActive,
        ];
    }
}