<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

class PaymentIndexPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Collection) {
            return parent::present($data);
        }

        /** @var Collection<int, Payment> $data */
        return $data->values()->map(fn (Payment $payment): array => [
            'id' => $payment->id,
            'subscription_id' => $payment->subscription_id,
            'provider' => $payment->provider,
            'provider_payment_id' => $payment->provider_payment_id,
            'status' => $payment->status,
            'amount_minor' => $payment->amount_minor,
            'currency' => $payment->currency,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'created_at' => $payment->created_at?->toIso8601String(),
            'updated_at' => $payment->updated_at?->toIso8601String(),
        ])->toArray();
    }
}
