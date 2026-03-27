<?php

declare(strict_types=1);

namespace App\Modules\Plans\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Plans\Enums\Permission as PlanPermission;

class DeletePlanRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(PlanPermission::DELETE_PLANS->value) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
