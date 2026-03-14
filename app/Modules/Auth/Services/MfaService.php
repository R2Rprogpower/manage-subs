<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Exceptions\ForbiddenException;
use App\Core\Exceptions\UnauthorizedException;
use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Auth\DTO\MfaSetupDTO;
use App\Modules\Auth\DTO\MfaVerifyDTO;
use App\Modules\Auth\Repositories\AuthUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class MfaService
{
    public function __construct(
        private readonly AuthUserRepository $userRepository,
        private readonly AuditLogService $auditLogService,
        private readonly Google2FA $google2FA
    ) {}

    /**
     * @return array{secret: string, otpauth_url: string, recovery_codes: array<int, string>}
     */
    public function setup(MfaSetupDTO $dto, Request $request): array
    {
        $user = $this->getUserByCredentials($dto->email, $dto->password);

        if ($user->mfa_enabled_at !== null) {
            throw new ForbiddenException('MFA already enabled.');
        }

        $secret = $this->google2FA->generateSecretKey();
        $recoveryCodes = $this->generateRecoveryCodes();
        $hashedRecoveryCodes = array_map(static fn (string $code): string => hash('sha256', $code), $recoveryCodes);

        $user->forceFill([
            'mfa_secret' => $secret,
            'mfa_recovery_codes' => $hashedRecoveryCodes,
            'mfa_enabled_at' => null,
        ]);
        $this->userRepository->save($user);

        $otpauthUrl = $this->google2FA->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $this->auditLogService->logMfaSetup($user, $request);

        return [
            'secret' => $secret,
            'otpauth_url' => $otpauthUrl,
            'recovery_codes' => $recoveryCodes,
        ];
    }

    public function verify(MfaVerifyDTO $dto, Request $request): bool
    {
        $user = $this->getUserByCredentials($dto->email, $dto->password);

        if ($user->mfa_secret === null) {
            throw new ForbiddenException('MFA setup required for this operation.');
        }

        $verified = $this->verifyToken($user, $dto->mfaToken);

        if (! $verified) {
            $this->auditLogService->logMfaVerificationFailure($user, $request);
            throw new ForbiddenException('MFA verification required for this operation.');
        }

        if ($user->mfa_enabled_at === null) {
            $user->forceFill(['mfa_enabled_at' => now()]);
            $this->userRepository->save($user);
        }

        $this->auditLogService->logMfaVerified($user, $request);

        return true;
    }

    public function verifyToken(User $user, string $token): bool
    {
        if ($user->mfa_secret === null) {
            return false;
        }

        if ($this->google2FA->verifyKey($user->mfa_secret, $token)) {
            return true;
        }

        return $this->verifyRecoveryCode($user, $token);
    }

    private function verifyRecoveryCode(User $user, string $token): bool
    {
        $recoveryCodes = $user->mfa_recovery_codes ?? [];
        $hashedToken = hash('sha256', $token);

        $index = array_search($hashedToken, $recoveryCodes, true);
        if ($index === false) {
            return false;
        }

        unset($recoveryCodes[$index]);
        $user->forceFill(['mfa_recovery_codes' => array_values($recoveryCodes)]);
        $this->userRepository->save($user);

        return true;
    }

    /**
     * @return array<int, string>
     */
    private function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(10));
        }

        return $codes;
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
