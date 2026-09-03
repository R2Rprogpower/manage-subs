<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Models\Subscription;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionMineProcessor extends Processor
{
    public function __construct(private readonly SubscriptionServiceInterface $service) {}

    /** @return Collection<int, Subscription> */
    public function execute(int $userId): Collection
    {
        return $this->service->findByUserId($userId);
    }
}
