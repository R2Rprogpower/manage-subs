<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Core\Abstracts\Request;

class TokenRevokeRequest extends Request
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
            'token_id' => ['nullable', 'integer'],
        ];
    }
}
