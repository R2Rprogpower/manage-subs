<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\Subscription;

class SubscriptionPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Subscription) {
            return parent::present($data);
        }

        return [
            'id' => $data->id,
            'user_id' => $data->user_id,
            'plan_id' => $data->plan_id,
            'status' => $data->status,
            'started_at' => $data->started_at->toIso8601String(),
            'ends_at' => $data->ends_at?->toIso8601String(),
            'auto_renew' => $data->auto_renew,
            'trial_used' => $data->trial_used,
            'source' => $data->source,
            'created_at' => $data->created_at?->toIso8601String(),
            'updated_at' => $data->updated_at?->toIso8601String(),
            'user' => $data->relationLoaded('user') && $data->user !== null ? [
                'id' => $data->user->id,
                'email' => $data->user->email,
            ] : null,
            'plan' => $data->relationLoaded('plan') && $data->plan !== null ? [
                'id' => $data->plan->id,
                'code' => $data->plan->code,
                'name' => $data->plan->name,
            ] : null,
            'payments_count' => $data->relationLoaded('payments') ? $data->payments->count() : null,
        ];
    }
}
