<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\Subscription;
use App\Modules\Subscriptions\DTO\CreateSubscriptionDTO;
use App\Modules\Subscriptions\Services\SubscriptionService;

class SubscriptionStoreProcessor extends Processor
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function execute(BaseRequest $request): Subscription
    {
        $validated = $request->validated();

        return $this->subscriptionService->create(new CreateSubscriptionDTO(
            userId: (int) $validated['user_id'],
            planId: (int) $validated['plan_id'],
            status: $validated['status'],
            startedAt: $validated['started_at'],
            endsAt: $validated['ends_at'] ?? null,
            autoRenew: (bool) $validated['auto_renew'],
            trialUsed: (bool) $validated['trial_used'],
            source: $validated['source'],
        ));
    }
}