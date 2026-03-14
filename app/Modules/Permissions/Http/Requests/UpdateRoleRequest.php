<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Http\Requests;

use App\Core\Abstracts\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateRoleRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(\App\Modules\Permissions\Enums\Permission::MANAGE_ROLES->value) ?? false;
    }

    /**
     * @return array<string, array<int, string|Rule|Unique>>
     */
    public function rules(): array
    {
        $roleId = $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)],
            'guard_name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
