<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Users\Enums\Permission as UserPermission;
use Illuminate\Validation\Rule;

class StoreUserRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(UserPermission::CREATE_USERS->value) ?? false;
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
