<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Contracts\Services;

use App\Models\Subscription;
use DateTimeInterface;

interface AccessPolicyServiceInterface
{
    public function shouldHaveChannelAccess(int $userId, ?DateTimeInterface $at = null): bool;

    public function evaluateSubscriptionAccess(Subscription $subscription, ?DateTimeInterface $at = null): bool;
}
