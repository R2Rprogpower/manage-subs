<?php

declare(strict_types=1);

namespace App\Modules\Payments\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Payments\Enums\Permission as PaymentPermission;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(PaymentPermission::UPDATE_PAYMENTS->value) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'subscription_id' => ['sometimes', 'integer', 'exists:subscriptions,id'],
            'provider' => ['sometimes', 'string', 'max:100'],
            'provider_payment_id' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'paid', 'failed'])],
            'amount_minor' => ['sometimes', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}
