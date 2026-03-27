<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\Payment;

class PaymentPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Payment) {
            return parent::present($data);
        }

        return [
            'id' => $data->id,
            'subscription_id' => $data->subscription_id,
            'provider' => $data->provider,
            'provider_payment_id' => $data->provider_payment_id,
            'status' => $data->status,
            'amount_minor' => $data->amount_minor,
            'currency' => $data->currency,
            'paid_at' => $data->paid_at?->toIso8601String(),
            'created_at' => $data->created_at?->toIso8601String(),
            'updated_at' => $data->updated_at?->toIso8601String(),
            'subscription' => $data->relationLoaded('subscription') && $data->subscription !== null ? [
                'id' => $data->subscription->id,
                'user_id' => $data->subscription->user_id,
                'plan_id' => $data->subscription->plan_id,
                'status' => $data->subscription->status,
            ] : null,
        ];
    }
}
