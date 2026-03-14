<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Exceptions\ForbiddenException;
use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Auth\DTO\MfaSetupDTO;
use App\Modules\Auth\DTO\MfaVerifyDTO;
use App\Modules\Auth\Repositories\AuthUserRepository;
use App\Modules\Auth\Services\MfaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Mockery;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class MfaServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_setup_generates_secret_and_recovery_codes_and_saves_user(): void
    {
        $user = $this->makeUser();
        $request = Request::create('/api/auth/mfa/setup', 'POST');
        $dto = new MfaSetupDTO(email: 'test@example.com', password: 'password123');

        $repository = Mockery::mock(AuthUserRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $google2FA = Mockery::mock(Google2FA::class);

        $repository->shouldReceive('findByEmail')->once()->with('test@example.com')->andReturn($user);
        $google2FA->shouldReceive('generateSecretKey')->once()->andReturn('SECRET123');
        $google2FA->shouldReceive('getQRCodeUrl')->once()->with(config('app.name'), 'test@example.com', 'SECRET123')->andReturn('otpauth://test-url');
        $repository->shouldReceive('save')->once()->with($user);
        $auditLogService->shouldReceive('logMfaSetup')->once()->with($user, $request);

        $service = new MfaService($repository, $auditLogService, $google2FA);

        $result = $service->setup($dto, $request);

        $this->assertSame('SECRET123', $result['secret']);
        $this->assertSame('otpauth://test-url', $result['otpauth_url']);
        $this->assertCount(8, $result['recovery_codes']);

        $this->assertSame('SECRET123', $user->mfa_secret);
        $this->assertCount(8, $user->mfa_recovery_codes ?? []);

        foreach ($result['recovery_codes'] as $index => $code) {
            $this->assertSame(hash('sha256', $code), $user->mfa_recovery_codes[$index]);
        }
    }

    public function test_verify_enables_mfa_when_token_is_valid(): void
    {
        $user = $this->makeUser(mfaSecret: 'SECRET123', mfaEnabledAt: null);
        $request = Request::create('/api/auth/mfa/verify', 'POST');
        $dto = new MfaVerifyDTO(email: 'test@example.com', password: 'password123', mfaToken: '123456');

        $repository = Mockery::mock(AuthUserRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $google2FA = Mockery::mock(Google2FA::class);

        $repository->shouldReceive('findByEmail')->once()->with('test@example.com')->andReturn($user);
        $google2FA->shouldReceive('verifyKey')->once()->with('SECRET123', '123456')->andReturn(true);
        $repository->shouldReceive('save')->once()->with($user);
        $auditLogService->shouldReceive('logMfaVerified')->once()->with($user, $request);
        $auditLogService->shouldNotReceive('logMfaVerificationFailure');

        $service = new MfaService($repository, $auditLogService, $google2FA);

        $verified = $service->verify($dto, $request);

        $this->assertTrue($verified);
        $this->assertNotNull($user->mfa_enabled_at);
    }

    public function test_verify_logs_failure_and_throws_when_token_is_invalid(): void
    {
        $user = $this->makeUser(mfaSecret: 'SECRET123', mfaEnabledAt: Carbon::now(), recoveryCodes: []);
        $request = Request::create('/api/auth/mfa/verify', 'POST');
        $dto = new MfaVerifyDTO(email: 'test@example.com', password: 'password123', mfaToken: '000000');

        $repository = Mockery::mock(AuthUserRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $google2FA = Mockery::mock(Google2FA::class);

        $repository->shouldReceive('findByEmail')->once()->with('test@example.com')->andReturn($user);
        $google2FA->shouldReceive('verifyKey')->once()->with('SECRET123', '000000')->andReturn(false);
        $auditLogService->shouldReceive('logMfaVerificationFailure')->once()->with($user, $request);
        $auditLogService->shouldNotReceive('logMfaVerified');
        $repository->shouldNotReceive('save');

        $service = new MfaService($repository, $auditLogService, $google2FA);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('MFA verification required for this operation.');

        $service->verify($dto, $request);
    }

    public function test_recovery_code_is_one_time_use(): void
    {
        $recoveryCode = 'RECOVERY01';
        $hashed = hash('sha256', $recoveryCode);

        $user = $this->makeUser(
            mfaSecret: 'SECRET123',
            mfaEnabledAt: Carbon::now(),
            recoveryCodes: [$hashed]
        );

        $repository = Mockery::mock(AuthUserRepository::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $google2FA = Mockery::mock(Google2FA::class);

        $google2FA->shouldReceive('verifyKey')->twice()->with('SECRET123', $recoveryCode)->andReturn(false);
        $repository->shouldReceive('save')->once()->with($user);

        $service = new MfaService($repository, $auditLogService, $google2FA);

        $firstTry = $service->verifyToken($user, $recoveryCode);
        $secondTry = $service->verifyToken($user, $recoveryCode);

        $this->assertTrue($firstTry);
        $this->assertFalse($secondTry);
        $this->assertSame([], $user->mfa_recovery_codes);
    }

    /**
     * @param  array<int, string>|null  $recoveryCodes
     */
    private function makeUser(?string $mfaSecret = null, ?Carbon $mfaEnabledAt = null, ?array $recoveryCodes = null): User
    {
        $user = new User;
        $user->id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = Hash::make('password123');
        $user->mfa_secret = $mfaSecret;
        $user->mfa_enabled_at = $mfaEnabledAt;
        $user->mfa_recovery_codes = $recoveryCodes ?? [];

        return $user;
    }
}
