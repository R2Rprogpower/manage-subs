<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionIndexProcessor extends Processor
{
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptionService
    ) {}

    /**
     * @return Collection<int, \App\Models\Subscription>
     */
    public function execute(): Collection
    {
        return $this->subscriptionService->findAll();
    }
}
