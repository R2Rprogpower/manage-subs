<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionIndexPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Collection) {
            return parent::present($data);
        }

        /** @var Collection<int, Subscription> $data */
        return $data->values()->map(fn (Subscription $subscription): array => [
            'id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'plan_id' => $subscription->plan_id,
            'status' => $subscription->status,
            'started_at' => $subscription->started_at?->toIso8601String(),
            'ends_at' => $subscription->ends_at?->toIso8601String(),
            'auto_renew' => $subscription->auto_renew,
            'trial_used' => $subscription->trial_used,
            'source' => $subscription->source,
            'created_at' => $subscription->created_at?->toIso8601String(),
            'updated_at' => $subscription->updated_at?->toIso8601String(),
        ])->toArray();
    }
}