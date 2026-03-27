<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\Subscription;
use App\Modules\Subscriptions\DTO\UpdateSubscriptionDTO;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;

class SubscriptionUpdateProcessor extends Processor
{
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptionService
    ) {}

    public function execute(BaseRequest $request, int $id): Subscription
    {
        $validated = $request->validated();

        $data = [];

        if (array_key_exists('user_id', $validated)) {
            $data['user_id'] = (int) $validated['user_id'];
        }

        if (array_key_exists('plan_id', $validated)) {
            $data['plan_id'] = (int) $validated['plan_id'];
        }

        if (array_key_exists('status', $validated)) {
            $data['status'] = $validated['status'];
        }

        if (array_key_exists('started_at', $validated)) {
            $data['started_at'] = $validated['started_at'];
        }

        if (array_key_exists('ends_at', $validated)) {
            $data['ends_at'] = $validated['ends_at'];
        }

        if (array_key_exists('auto_renew', $validated)) {
            $data['auto_renew'] = (bool) $validated['auto_renew'];
        }

        if (array_key_exists('trial_used', $validated)) {
            $data['trial_used'] = (bool) $validated['trial_used'];
        }

        if (array_key_exists('source', $validated)) {
            $data['source'] = $validated['source'];
        }

        return $this->subscriptionService->update($id, new UpdateSubscriptionDTO($data));
    }
}