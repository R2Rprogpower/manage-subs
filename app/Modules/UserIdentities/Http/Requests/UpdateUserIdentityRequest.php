<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\UserIdentities\Enums\Permission as UserIdentityPermission;
use Illuminate\Validation\Rule;

class UpdateUserIdentityRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(UserIdentityPermission::UPDATE_USER_IDENTITIES->value) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'provider' => ['sometimes', 'string', 'max:100'],
            'provider_user_id' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('user_identities', 'provider_user_id')
                    ->ignore($id)
                    ->where(fn ($query) => $query->where('provider', $this->input('provider', ''))),
            ],
            'username' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
