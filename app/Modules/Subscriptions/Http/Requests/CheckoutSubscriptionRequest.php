<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Http\Requests;

use App\Core\Abstracts\Request;

class CheckoutSubscriptionRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'confirm_placeholder' => ['required', 'accepted'],
            'card_number' => ['prohibited'],
            'card_cvv' => ['prohibited'],
            'cvv' => ['prohibited'],
            'card_expiry' => ['prohibited'],
            'cardholder_name' => ['prohibited'],
            'bank_account' => ['prohibited'],
            'iban' => ['prohibited'],
            'routing_number' => ['prohibited'],
        ];
    }
}
