<?php

declare(strict_types=1);

namespace App\Modules\Plans\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Plans\Enums\Permission as PlanPermission;
use Illuminate\Validation\Rule;

class StorePlanRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(PlanPermission::CREATE_PLANS->value) ?? false;
    }

    /**
     * @return array<string, list<string|Rule>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'unique:plans,code'],
            'name' => ['required', 'string', 'max:255'],
            'price_minor' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
