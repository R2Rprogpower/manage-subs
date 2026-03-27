<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;

class SubscriptionDestroyProcessor extends Processor
{
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptionService
    ) {}

    public function execute(BaseRequest $request, int $id): bool
    {
        $this->subscriptionService->delete($id);

        return true;
    }
}
