<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Models\Subscription;
use App\Models\User;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;
use App\Modules\Subscriptions\Http\Requests\GrantFreeSubscriptionRequest;

class GrantFreeSubscriptionProcessor extends Processor
{
    public function __construct(private readonly SubscriptionServiceInterface $service) {}

    public function execute(GrantFreeSubscriptionRequest $request): Subscription
    {
        $user = $request->user();
        if (! $user instanceof User) {
            throw new \LogicException('Authenticated user is required.');
        }
        $data = $request->validated();

        return $this->service->grantFreeAccess((int) $data['user_id'], (int) $data['plan_id'], $user->id);
    }
}
