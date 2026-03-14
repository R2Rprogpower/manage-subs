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

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/auth/logout')
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
}
