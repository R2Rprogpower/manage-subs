<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\DTO;

readonly class CreateSubscriptionDTO
{
    public function __construct(
        public int $userId,
        public int $planId,
        public string $status,
        public string $startedAt,
        public ?string $endsAt,
        public bool $autoRenew,
        public bool $trialUsed,
        public string $source,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'plan_id' => $this->planId,
            'status' => $this->status,
            'started_at' => $this->startedAt,
            'ends_at' => $this->endsAt,
            'auto_renew' => $this->autoRenew,
            'trial_used' => $this->trialUsed,
            'source' => $this->source,
        ];
    }
}
