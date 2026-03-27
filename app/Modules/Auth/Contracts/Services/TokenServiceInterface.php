<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts\Services;

use App\Models\User;
use Illuminate\Http\Request;

interface TokenServiceInterface
{
    /**
     * @param  array<int, string>  $abilities
     */
    public function issueToken(User $user, string $name, array $abilities = ['*']): string;

    public function revokeCurrentToken(User $user, Request $request): void;

    public function revokeTokenById(User $user, int $tokenId): void;

    public function revokeAllTokens(User $user): void;
}
