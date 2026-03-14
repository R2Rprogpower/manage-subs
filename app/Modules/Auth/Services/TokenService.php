<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenService
{
    /**
     * @param  array<int, string>  $abilities
     */
    public function issueToken(User $user, string $name, array $abilities = ['*']): string
    {
        return $user->createToken($name, $abilities)->plainTextToken;
    }

    public function revokeCurrentToken(User $user, Request $request): void
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    public function revokeTokenById(User $user, int $tokenId): void
    {
        $token = $user->tokens()->whereKey($tokenId)->first();

        if ($token) {
            $token->delete();
        }
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
