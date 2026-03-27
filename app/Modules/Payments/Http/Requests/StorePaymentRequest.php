<?php

declare(strict_types=1);

namespace App\Modules\Payments\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Payments\Enums\Permission as PaymentPermission;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(PaymentPermission::CREATE_PAYMENTS->value) ?? false;
    }

    /**
     * @return array<string, list<string|Rule>>
     */
    public function rules(): array
    {
        return [
            'subscription_id' => ['required', 'integer', 'exists:subscriptions,id'],
            'provider' => ['required', 'string', 'max:100'],
            'provider_payment_id' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['pending', 'paid', 'failed'])],
            'amount_minor' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}