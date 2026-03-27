<?php

declare(strict_types=1);

namespace App\Modules\Plans\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Plans\Enums\Permission as PlanPermission;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(PlanPermission::UPDATE_PLANS->value) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $planId = (int) $this->route('id');

        return [
            'code' => ['sometimes', 'string', 'max:100', Rule::unique('plans', 'code')->ignore($planId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'price_minor' => ['sometimes', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
