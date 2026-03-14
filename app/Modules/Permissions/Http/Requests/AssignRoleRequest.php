<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Permissions\Enums\Permission;

class AssignRoleRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::MANAGE_ROLES->value) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'role_name' => ['required', 'string', 'exists:roles,name'],
        ];
    }
}
