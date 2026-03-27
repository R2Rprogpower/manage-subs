<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionIndexProcessor extends Processor
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    /**
     * @return Collection<int, \App\Models\Subscription>
     */
    public function execute(): Collection
    {
        return $this->subscriptionService->findAll();
    }
}