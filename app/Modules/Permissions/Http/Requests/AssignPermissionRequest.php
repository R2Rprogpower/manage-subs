<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Permissions\Enums\Permission;

class AssignPermissionRequest extends Request
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
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['required', 'integer', 'exists:permissions,id'],
        ];
    }
}
