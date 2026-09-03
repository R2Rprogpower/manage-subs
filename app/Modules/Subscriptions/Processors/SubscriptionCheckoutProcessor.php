<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Models\Subscription;
use App\Models\User;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;
use App\Modules\Subscriptions\Http\Requests\CheckoutSubscriptionRequest;

class SubscriptionCheckoutProcessor extends Processor
{
    public function __construct(private readonly SubscriptionServiceInterface $service) {}

    public function execute(CheckoutSubscriptionRequest $request): Subscription
    {
        $user = $request->user();
        if (! $user instanceof User) {
            throw new \LogicException('Authenticated user is required.');
        }

        return $this->service->subscribeWithPlaceholder($user->id, (int) $request->validated('plan_id'));
    }
}
