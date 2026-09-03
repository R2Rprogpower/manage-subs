<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Subscriptions\Enums\Permission as SubscriptionPermission;
use App\Modules\Subscriptions\Enums\SubscriptionSource;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(SubscriptionPermission::UPDATE_SUBSCRIPTIONS->value) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'plan_id' => ['sometimes', 'integer', 'exists:plans,id'],
            'status' => ['prohibited'],
            'started_at' => ['sometimes', 'date'],
            'ends_at' => ['nullable', 'date'],
            'auto_renew' => ['sometimes', 'boolean'],
            'trial_used' => ['sometimes', 'boolean'],
            'source' => ['sometimes', 'string', Rule::in(SubscriptionSource::values())],
        ];
    }
}
