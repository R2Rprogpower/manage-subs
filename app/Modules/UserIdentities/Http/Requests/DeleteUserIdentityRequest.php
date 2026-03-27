<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\UserIdentities\Enums\Permission as UserIdentityPermission;

class DeleteUserIdentityRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(UserIdentityPermission::DELETE_USER_IDENTITIES->value) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}