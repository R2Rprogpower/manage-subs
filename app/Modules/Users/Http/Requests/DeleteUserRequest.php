<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Users\Enums\Permission as UserPermission;

class DeleteUserRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(UserPermission::DELETE_USERS->value) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
