<?php

declare(strict_types=1);

namespace App\Modules\Payments\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Payments\Enums\Permission as PaymentPermission;
use Illuminate\Validation\Rule;

class CreateLiqPayCheckoutRequest extends Request
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
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'description' => ['sometimes', 'string', 'max:255'],
            'result_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'server_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'action' => ['sometimes', 'string', Rule::in(['pay', 'hold'])],
            'sandbox' => ['sometimes', 'boolean'],
        ];
    }
}
