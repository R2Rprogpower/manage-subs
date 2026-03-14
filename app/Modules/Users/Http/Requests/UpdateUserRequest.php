<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Users\Enums\Permission as UserPermission;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(UserPermission::UPDATE_USERS->value) ?? false;
    }

    /**
     * @return array<string, list<string|Rule>>
     */
    public function rules(): array
    {
        $userId = $this->route('id');

        /** @var array<string, list<string|Rule>> */
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
        ];
    }
}
