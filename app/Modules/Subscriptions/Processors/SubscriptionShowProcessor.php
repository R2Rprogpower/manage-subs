<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Exceptions\BaseException;
use App\Models\Subscription;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;

class SubscriptionShowProcessor extends Processor
{
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptionService
    ) {}

    public function execute(int $id): Subscription
    {
        $subscription = $this->subscriptionService->findById($id);

        if (! $subscription) {
            throw new class("Subscription with ID {$id} not found.") extends BaseException
            {
                public function getStatusCode(): int
                {
                    return 404;
                }
            };
        }

        return $subscription;
    }
}