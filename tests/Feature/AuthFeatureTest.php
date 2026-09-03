<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_creates_user(): void
    {
        $response = $this->postJson('/api/auth/signup', [
            'name' => 'Test User',
            'email' => 'signup@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'signup@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'signup@example.com',
        ]);
    }

    public function test_login_returns_access_token_when_mfa_not_enabled(): void
    {
        User::query()->create([
            'name' => 'Login User',
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.user.email', 'login@example.com')
            ->assertJsonPath('data.token_type', 'Bearer');

        $this->assertIsString((string) $response->json('data.access_token'));
        $this->assertNotEmpty((string) $response->json('data.access_token'));
    }

    public function test_web_login_authenticates_the_session(): void
    {
        $user = User::query()->create([
            'name' => 'Web Login User',
            'email' => 'web-login@example.com',
            'password' => 'password123',
        ]);

        $this->get('/login')->assertOk();

        $this->postJson('/login', [
            '_token' => session()->token(),
            'email' => 'web-login@example.com',
            'password' => 'password123',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_requires_authentication(): void
    {
        $this->post('/api/auth/logout')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_logout_returns_success_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('data.logged_out', true);
    }

    public function test_login_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/login', [
                'email' => 'rate-limited@example.com',
                'password' => 'password123',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/auth/login', [
            'email' => 'rate-limited@example.com',
            'password' => 'password123',
        ])->assertTooManyRequests();
    }

    public function test_responses_include_security_headers(): void
    {
        $this->withHeader('X-Forwarded-Proto', 'https')
            ->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Strict-Transport-Security');
    }
}
