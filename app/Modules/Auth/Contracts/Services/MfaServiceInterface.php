<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts\Services;

use App\Models\User;
use App\Modules\Auth\DTO\MfaSetupDTO;
use App\Modules\Auth\DTO\MfaVerifyDTO;
use Illuminate\Http\Request;

interface MfaServiceInterface
{
    /**
     * @return array{secret: string, otpauth_url: string, recovery_codes: array<int, string>}
     */
    public function setup(MfaSetupDTO $dto, Request $request): array;

    public function verify(MfaVerifyDTO $dto, Request $request): bool;

    public function verifyToken(User $user, string $token): bool;
}