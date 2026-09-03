<?php

declare(strict_types=1);

namespace App\Modules\Plans\Http\Requests;

use App\Core\Abstracts\Request;
use App\Models\TelegramChannel;
use App\Modules\Plans\Enums\Permission as PlanPermission;
use App\Modules\Plans\Enums\PlanKind;
use Illuminate\Validation\Rule;

class StorePlanRequest extends Request
{
    public function authorize(): bool
    {
        $user = $this->user();
        $channel = TelegramChannel::query()->find((int) $this->input('telegram_channel_id'));

        return $user !== null && $channel !== null && (
            $channel->owner_id === $user->id || $user->can(PlanPermission::CREATE_PLANS->value)
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'telegram_channel_id' => ['required', 'integer', 'exists:telegram_channels,id'],
            'code' => ['required', 'string', 'max:100', 'unique:plans,code'],
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'string', Rule::in(PlanKind::values())],
            'price_minor' => ['required_if:kind,money', 'nullable', 'integer', 'min:0'],
            'currency' => ['required_if:kind,money', 'nullable', 'string', 'size:3'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'configuration' => ['required_if:kind,achievement', 'nullable', 'array'],
            'configuration.achievement_key' => ['required_if:kind,achievement', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
