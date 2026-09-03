<?php

declare(strict_types=1);

namespace App\Modules\Plans\Http\Requests;

use App\Core\Abstracts\Request;
use App\Models\Plan;
use App\Modules\Plans\Enums\Permission as PlanPermission;

class DeletePlanRequest extends Request
{
    public function authorize(): bool
    {
        $user = $this->user();
        $plan = Plan::query()->with('telegramChannel')->find((int) $this->route('id'));

        return $user !== null && $plan !== null && (
            $plan->telegramChannel?->owner_id === $user->id || $user->can(PlanPermission::DELETE_PLANS->value)
        );
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
