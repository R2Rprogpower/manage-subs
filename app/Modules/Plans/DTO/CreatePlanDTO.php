<?php

declare(strict_types=1);

namespace App\Modules\Plans\DTO;

readonly class CreatePlanDTO
{
    public function __construct(
        public int $telegramChannelId,
        public string $code,
        public string $name,
        public string $kind,
        public int $priceMinor,
        public string $currency,
        public ?int $durationDays,
        /** @var array<string, mixed>|null */
        public ?array $configuration,
        public bool $isActive,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'telegram_channel_id' => $this->telegramChannelId,
            'code' => $this->code,
            'name' => $this->name,
            'kind' => $this->kind,
            'price_minor' => $this->priceMinor,
            'currency' => strtoupper($this->currency),
            'duration_days' => $this->durationDays,
            'configuration' => $this->configuration,
            'is_active' => $this->isActive,
        ];
    }
}
