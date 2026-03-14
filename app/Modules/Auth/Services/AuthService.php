<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Exceptions\ForbiddenException;
use App\Core\Exceptions\UnauthorizedException;
use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Auth\DTO\LoginDTO;
use App\Modules\Auth\DTO\TokenRevokeDTO;
use App\Modules\Auth\Repositories\AuthUserRepository;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly AuthUserRepository $userRepository,
        private readonly TokenService $tokenService,
        private readonly MfaService $mfaService,
        private readonly AuditLogService $auditLogService
    ) {}

    /**
     * @return array{user: User, token: string}
     */
    public function login(LoginDTO $dto, Request $request): array
    {
        $user = $this->getUserByCredentials($dto->email, $dto->password);

        if ($user->mfa_enabled_at !== null) {
            if (! $dto->mfaToken) {
                throw new ForbiddenException('MFA verification required for this operation.');
            }

            if (! $this->mfaService->verifyToken($user, $dto->mfaToken)) {
                $this->auditLogService->logMfaVerificationFailure($user, $request);
                throw new ForbiddenException('MFA verification required for this operation.');
            }
        }

        $guard = Auth::guard('web');
        if ($guard instanceof StatefulGuard) {
            $guard->login($user);
        }
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $token = $this->tokenService->issueToken($user, 'api');
        $this->auditLogService->logAuthLogin($user, $request);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(Request $request): void
    {
        $user = $request->user();
        if (! $user instanceof User) {
            throw new UnauthorizedException('Unauthenticated.');
        }

        $this->tokenService->revokeCurrentToken($user, $request);
        $guard = Auth::guard('web');
        if ($guard instanceof StatefulGuard) {
            $guard->logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $this->auditLogService->logAuthLogout($user, $request);
    }

    public function revokeToken(TokenRevokeDTO $dto, Request $request): void
    {
        $user = $request->user();
        if (! $user instanceof User) {
            throw new UnauthorizedException('Unauthenticated.');
        }

        if ($dto->tokenId !== null) {
            $this->tokenService->revokeTokenById($user, $dto->tokenId);
        } else {
            $this->tokenService->revokeCurrentToken($user, $request);
        }

        $this->auditLogService->logTokenRevoked($user, $request, $dto->tokenId);
    }

    private function getUserByCredentials(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new UnauthorizedException('Invalid credentials.');
        }

        return $user;
    }
}
