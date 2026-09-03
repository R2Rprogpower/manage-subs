<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Exceptions\ForbiddenException;
use App\Core\Exceptions\UnauthorizedException;
use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Auth\Contracts\Repositories\AuthUserRepositoryInterface;
use App\Modules\Auth\Contracts\Services\AuthServiceInterface;
use App\Modules\Auth\Contracts\Services\MfaServiceInterface;
use App\Modules\Auth\Contracts\Services\TokenServiceInterface;
use App\Modules\Auth\DTO\LoginDTO;
use App\Modules\Auth\DTO\TokenRevokeDTO;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly AuthUserRepositoryInterface $userRepository,
        private readonly TokenServiceInterface $tokenService,
        private readonly MfaServiceInterface $mfaService,
        private readonly AuditLogService $auditLogService
    ) {}

    /**
     * @return array{user: User, token: string}
     */
    public function login(LoginDTO $dto, Request $request): array
    {
        $stage = 'credentials';

        try {
            $user = $this->getUserByCredentials($dto->email, $dto->password);

            $stage = 'mfa';
            if ($user->mfa_enabled_at !== null) {
                if (! $dto->mfaToken) {
                    throw new ForbiddenException('MFA verification required for this operation.');
                }

                if (! $this->mfaService->verifyToken($user, $dto->mfaToken)) {
                    $this->auditLogService->logMfaVerificationFailure($user, $request);
                    throw new ForbiddenException('MFA verification required for this operation.');
                }
            }

            $stage = 'session';
            $guard = Auth::guard('web');
            if ($guard instanceof StatefulGuard) {
                $guard->login($user);
            }
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $stage = 'token';
            $token = $this->tokenService->issueToken($user, 'api');

            $stage = 'audit_log';
            $this->auditLogService->logAuthLogin($user, $request);

            Log::info('auth.login.succeeded', [
                'user_id' => $user->getKey(),
                'ip' => $request->ip(),
            ]);

            return ['user' => $user, 'token' => $token];
        } catch (Throwable $exception) {
            $context = [
                'stage' => $stage,
                'ip' => $request->ip(),
                'exception' => $exception,
            ];

            if ($exception instanceof UnauthorizedException || $exception instanceof ForbiddenException) {
                Log::warning('auth.login.rejected', $context);
            } else {
                Log::error('auth.login.failed', $context);
            }

            throw $exception;
        }
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
