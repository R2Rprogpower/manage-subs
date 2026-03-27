<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Subscriptions\Enums\Permission as SubscriptionPermission;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(SubscriptionPermission::UPDATE_SUBSCRIPTIONS->value) ?? false;
    }

    /**
     * @return array<string, list<string|Rule>>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'plan_id' => ['sometimes', 'integer', 'exists:plans,id'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'expired', 'cancelled'])],
            'started_at' => ['sometimes', 'date'],
            'ends_at' => ['nullable', 'date'],
            'auto_renew' => ['sometimes', 'boolean'],
            'trial_used' => ['sometimes', 'boolean'],
            'source' => ['sometimes', 'string', Rule::in(['bot', 'admin', 'manual'])],
        ];
    }
}