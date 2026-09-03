<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Events;

readonly class SubscriptionAccessChanged
{
    public function __construct(
        public int $subscriptionId,
        public int $userId,
        public int $channelId,
        public bool $shouldHaveAccess,
    ) {}
}
