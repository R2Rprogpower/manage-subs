<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Core\Abstracts\Request;

class MfaSetupRequest extends Request
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }
}
