<?php

declare(strict_types=1);

namespace App\Modules\Payments\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Payments\Enums\Permission as PaymentPermission;

class DeletePaymentRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(PaymentPermission::DELETE_PAYMENTS->value) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}