<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Exceptions\ForbiddenException;
use App\Infrastructure\Services\AuditLogService;
use App\Models\User;
use App\Modules\Auth\DTO\LoginDTO;
use App\Modules\Auth\Repositories\AuthUserRepository;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Services\MfaService;
use App\Modules\Auth\Services\TokenService;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_succeeds_without_mfa_when_user_has_not_enabled_it(): void
    {
        $user = $this->makeUser(mfaEnabledAt: null);
        $request = Request::create('/api/auth/login', 'POST');
        $dto = new LoginDTO(email: 'test@example.com', password: 'password123', mfaToken: null);

        $repository = Mockery::mock(AuthUserRepository::class);
        $tokenService = Mockery::mock(TokenService::class);
        $mfaService = Mockery::mock(MfaService::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $guard = Mockery::mock(StatefulGuard::class);

        $repository->shouldReceive('findByEmail')->once()->with('test@example.com')->andReturn($user);
        $mfaService->shouldNotReceive('verifyToken');
        $tokenService->shouldReceive('issueToken')->once()->with($user, 'api')->andReturn('token-no-mfa');
        $auditLogService->shouldReceive('logAuthLogin')->once()->with($user, $request);
        $guard->shouldReceive('login')->once()->with($user);
        Auth::shouldReceive('guard')->once()->with('web')->andReturn($guard);

        $service = new AuthService($repository, $tokenService, $mfaService, $auditLogService);

        $result = $service->login($dto, $request);

        $this->assertSame($user, $result['user']);
        $this->assertSame('token-no-mfa', $result['token']);
    }

    public function test_login_requires_mfa_token_when_user_has_enabled_mfa(): void
    {
        $user = $this->makeUser(mfaEnabledAt: Carbon::now());
        $request = Request::create('/api/auth/login', 'POST');
        $dto = new LoginDTO(email: 'test@example.com', password: 'password123', mfaToken: null);

        $repository = Mockery::mock(AuthUserRepository::class);
        $tokenService = Mockery::mock(TokenService::class);
        $mfaService = Mockery::mock(MfaService::class);
        $auditLogService = Mockery::mock(AuditLogService::class);

        $repository->shouldReceive('findByEmail')->once()->with('test@example.com')->andReturn($user);
        $mfaService->shouldNotReceive('verifyToken');
        $tokenService->shouldNotReceive('issueToken');
        $auditLogService->shouldNotReceive('logAuthLogin');
        Auth::shouldReceive('guard')->never();

        $service = new AuthService($repository, $tokenService, $mfaService, $auditLogService);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('MFA verification required for this operation.');

        $service->login($dto, $request);
    }

    public function test_login_requires_valid_mfa_token_when_enabled(): void
    {
        $user = $this->makeUser(mfaEnabledAt: Carbon::now());
        $request = Request::create('/api/auth/login', 'POST');
        $dto = new LoginDTO(email: 'test@example.com', password: 'password123', mfaToken: '123456');

        $repository = Mockery::mock(AuthUserRepository::class);
        $tokenService = Mockery::mock(TokenService::class);
        $mfaService = Mockery::mock(MfaService::class);
        $auditLogService = Mockery::mock(AuditLogService::class);

        $repository->shouldReceive('findByEmail')->once()->with('test@example.com')->andReturn($user);
        $mfaService->shouldReceive('verifyToken')->once()->with($user, '123456')->andReturn(false);
        $auditLogService->shouldReceive('logMfaVerificationFailure')->once()->with($user, $request);
        $tokenService->shouldNotReceive('issueToken');
        $auditLogService->shouldNotReceive('logAuthLogin');
        Auth::shouldReceive('guard')->never();

        $service = new AuthService($repository, $tokenService, $mfaService, $auditLogService);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('MFA verification required for this operation.');

        $service->login($dto, $request);
    }

    public function test_login_succeeds_with_valid_mfa_token_when_enabled(): void
    {
        $user = $this->makeUser(mfaEnabledAt: Carbon::now());
        $request = Request::create('/api/auth/login', 'POST');
        $dto = new LoginDTO(email: 'test@example.com', password: 'password123', mfaToken: '123456');

        $repository = Mockery::mock(AuthUserRepository::class);
        $tokenService = Mockery::mock(TokenService::class);
        $mfaService = Mockery::mock(MfaService::class);
        $auditLogService = Mockery::mock(AuditLogService::class);
        $guard = Mockery::mock(StatefulGuard::class);

        $repository->shouldReceive('findByEmail')->once()->with('test@example.com')->andReturn($user);
        $mfaService->shouldReceive('verifyToken')->once()->with($user, '123456')->andReturn(true);
        $tokenService->shouldReceive('issueToken')->once()->with($user, 'api')->andReturn('token-with-mfa');
        $auditLogService->shouldReceive('logAuthLogin')->once()->with($user, $request);
        $auditLogService->shouldNotReceive('logMfaVerificationFailure');
        $guard->shouldReceive('login')->once()->with($user);
        Auth::shouldReceive('guard')->once()->with('web')->andReturn($guard);

        $service = new AuthService($repository, $tokenService, $mfaService, $auditLogService);

        $result = $service->login($dto, $request);

        $this->assertSame($user, $result['user']);
        $this->assertSame('token-with-mfa', $result['token']);
    }

    private function makeUser(?Carbon $mfaEnabledAt): User
    {
        $user = new User;
        $user->id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = Hash::make('password123');
        $user->mfa_enabled_at = $mfaEnabledAt;

        return $user;
    }
}
