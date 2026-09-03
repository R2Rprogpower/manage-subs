<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionIndexPresentation extends Presentation implements PresentationInterface
{
    public function __construct(private readonly SubscriptionPresentation $subscriptionPresentation) {}

    /** @return array<int|string, mixed> */
    public function present(mixed $data): array
    {
        if (! $data instanceof Collection) {
            return parent::present($data);
        }

        /** @var Collection<int, Subscription> $data */
        return $data->map(fn (Subscription $subscription): array => $this->subscriptionPresentation->present($subscription))->values()->all();
    }
}
