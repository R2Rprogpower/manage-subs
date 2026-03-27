<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Subscriptions\Enums\Permission as SubscriptionPermission;

class DeleteSubscriptionRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(SubscriptionPermission::DELETE_SUBSCRIPTIONS->value) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
