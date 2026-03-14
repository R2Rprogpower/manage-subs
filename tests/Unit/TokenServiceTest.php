<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Modules\Auth\Services\TokenService;
use Illuminate\Http\Request;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_issue_token_returns_plain_text_token(): void
    {
        $user = Mockery::mock(User::class);
        $accessToken = Mockery::mock(PersonalAccessToken::class);
        $newAccessToken = new NewAccessToken($accessToken, 'plain-token-value');

        $user->shouldReceive('createToken')
            ->once()
            ->with('api', ['*'])
            ->andReturn($newAccessToken);

        $service = new TokenService;

        $token = $service->issueToken($user, 'api');

        $this->assertSame('plain-token-value', $token);
    }

    public function test_revoke_current_token_deletes_current_access_token_when_present(): void
    {
        $user = Mockery::mock(User::class);
        $request = Mockery::mock(Request::class);
        $authUser = Mockery::mock(User::class);
        $currentToken = Mockery::mock(PersonalAccessToken::class);

        $request->shouldReceive('user')->once()->andReturn($authUser);
        $authUser->shouldReceive('currentAccessToken')->once()->andReturn($currentToken);
        $currentToken->shouldReceive('delete')->once();

        $service = new TokenService;

        $service->revokeCurrentToken($user, $request);

        $this->assertTrue(true);
    }

    public function test_revoke_current_token_does_nothing_when_no_current_token(): void
    {
        $user = Mockery::mock(User::class);
        $request = Mockery::mock(Request::class);
        $authUser = Mockery::mock(User::class);

        $request->shouldReceive('user')->once()->andReturn($authUser);
        $authUser->shouldReceive('currentAccessToken')->once()->andReturn(null);

        $service = new TokenService;

        $service->revokeCurrentToken($user, $request);

        $this->assertTrue(true);
    }

    public function test_revoke_token_by_id_deletes_found_token(): void
    {
        $user = Mockery::mock(User::class);
        $tokensRelation = Mockery::mock();
        $token = Mockery::mock(PersonalAccessToken::class);

        $user->shouldReceive('tokens')->once()->andReturn($tokensRelation);
        $tokensRelation->shouldReceive('whereKey')->once()->with(77)->andReturnSelf();
        $tokensRelation->shouldReceive('first')->once()->andReturn($token);
        $token->shouldReceive('delete')->once();

        $service = new TokenService;

        $service->revokeTokenById($user, 77);

        $this->assertTrue(true);
    }

    public function test_revoke_all_tokens_calls_delete_on_tokens_relation(): void
    {
        $user = Mockery::mock(User::class);
        $tokensRelation = Mockery::mock();

        $user->shouldReceive('tokens')->once()->andReturn($tokensRelation);
        $tokensRelation->shouldReceive('delete')->once();

        $service = new TokenService;

        $service->revokeAllTokens($user);

        $this->assertTrue(true);
    }
}
