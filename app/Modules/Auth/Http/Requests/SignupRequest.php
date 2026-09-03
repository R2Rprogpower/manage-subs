<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Core\Abstracts\Request;
use Illuminate\Validation\Rules\Password;

class SignupRequest extends Request
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|object>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(10)->letters()->numbers(), 'confirmed'],
        ];
    }
}
