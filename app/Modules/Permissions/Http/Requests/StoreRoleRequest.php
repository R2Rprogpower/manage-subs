<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Http\Requests;

use App\Core\Abstracts\Request;

class StoreRoleRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(\App\Modules\Permissions\Enums\Permission::MANAGE_ROLES->value) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'guard_name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
