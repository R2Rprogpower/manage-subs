<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts\Services;

use App\Modules\Auth\DTO\LoginDTO;
use App\Modules\Auth\DTO\TokenRevokeDTO;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    /**
     * @return array{user: \App\Models\User, token: string}
     */
    public function login(LoginDTO $dto, Request $request): array;

    public function logout(Request $request): void;

    public function revokeToken(TokenRevokeDTO $dto, Request $request): void;
}
