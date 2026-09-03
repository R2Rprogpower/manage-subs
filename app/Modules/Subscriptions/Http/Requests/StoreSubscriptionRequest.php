<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Subscriptions\Enums\Permission as SubscriptionPermission;
use App\Modules\Subscriptions\Enums\SubscriptionSource;
use App\Modules\Subscriptions\Enums\SubscriptionStatus;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(SubscriptionPermission::CREATE_SUBSCRIPTIONS->value) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'status' => ['required', 'string', Rule::in([
                SubscriptionStatus::DRAFT->value,
                SubscriptionStatus::PENDING->value,
                SubscriptionStatus::ACTIVE->value,
            ])],
            'started_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'auto_renew' => ['required', 'boolean'],
            'trial_used' => ['required', 'boolean'],
            'source' => ['required', 'string', Rule::in(SubscriptionSource::values())],
        ];
    }
}
