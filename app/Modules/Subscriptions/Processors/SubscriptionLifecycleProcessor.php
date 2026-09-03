<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Models\Subscription;
use App\Models\User;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;
use App\Modules\Subscriptions\Http\Requests\ManageSubscriptionRequest;
use Illuminate\Support\Carbon;

class SubscriptionLifecycleProcessor extends Processor
{
    public function __construct(private readonly SubscriptionServiceInterface $service) {}

    public function activate(ManageSubscriptionRequest $request, int $id): Subscription
    {
        return $this->service->activateSubscription($id, $this->actorId($request));
    }

    public function pending(ManageSubscriptionRequest $request, int $id): Subscription
    {
        return $this->service->markPendingSubscription($id, $this->actorId($request));
    }

    public function cancel(ManageSubscriptionRequest $request, int $id): Subscription
    {
        return $this->service->cancelSubscription($id, $this->actorId($request));
    }

    public function suspend(ManageSubscriptionRequest $request, int $id): Subscription
    {
        return $this->service->suspendSubscription($id, $this->actorId($request));
    }

    public function renew(ManageSubscriptionRequest $request, int $id): Subscription
    {
        $endsAt = $request->validated('ends_at');

        return $this->service->renewSubscription($id, $endsAt !== null ? Carbon::parse($endsAt) : null, $this->actorId($request));
    }

    private function actorId(ManageSubscriptionRequest $request): int
    {
        $user = $request->user();
        if (! $user instanceof User) {
            throw new \LogicException('Authenticated user is required.');
        }

        return $user->id;
    }
}
