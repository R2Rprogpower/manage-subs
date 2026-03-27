<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\UserIdentities\Enums\Permission as UserIdentityPermission;
use Illuminate\Validation\Rule;

class StoreUserIdentityRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()?->can(UserIdentityPermission::CREATE_USER_IDENTITIES->value) ?? false;
    }

    /**
     * @return array<string, list<string|Rule>>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'provider' => ['required', 'string', 'max:100'],
            'provider_user_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user_identities', 'provider_user_id')->where(
                    fn ($query) => $query->where('provider', $this->input('provider'))
                ),
            ],
            'username' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ];
    }
}