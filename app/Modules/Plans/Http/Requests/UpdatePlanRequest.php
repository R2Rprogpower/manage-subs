<?php

declare(strict_types=1);

namespace App\Modules\Plans\Http\Requests;

use App\Core\Abstracts\Request;
use App\Models\Plan;
use App\Models\TelegramChannel;
use App\Modules\Plans\Enums\Permission as PlanPermission;
use App\Modules\Plans\Enums\PlanKind;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends Request
{
    public function authorize(): bool
    {
        $user = $this->user();
        $plan = Plan::query()->with('telegramChannel')->find((int) $this->route('id'));
        if ($user === null || $plan === null) {
            return false;
        }

        $canUpdateCurrent = $plan->telegramChannel?->owner_id === $user->id || $user->can(PlanPermission::UPDATE_PLANS->value);
        if (! $canUpdateCurrent) {
            return false;
        }

        if ($this->has('telegram_channel_id')) {
            $target = TelegramChannel::query()->find((int) $this->input('telegram_channel_id'));

            return $target !== null && ($target->owner_id === $user->id || $user->can(PlanPermission::UPDATE_PLANS->value));
        }

        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $planId = (int) $this->route('id');

        return [
            'telegram_channel_id' => ['sometimes', 'integer', 'exists:telegram_channels,id'],
            'code' => ['sometimes', 'string', 'max:100', Rule::unique('plans', 'code')->ignore($planId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'kind' => ['sometimes', 'string', Rule::in(PlanKind::values())],
            'price_minor' => ['sometimes', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'configuration' => ['required_if:kind,achievement', 'nullable', 'array'],
            'configuration.achievement_key' => ['required_if:kind,achievement', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
