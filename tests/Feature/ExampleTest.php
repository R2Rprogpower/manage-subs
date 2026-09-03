<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guest_is_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');

        $response->assertRedirectToRoute('login');
    }

    public function test_assets_use_forwarded_https_scheme_behind_reverse_proxy(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Host' => 'manage-subs.example.com',
            'X-Forwarded-Proto' => 'https',
        ])->get('/login');

        $response
            ->assertOk()
            ->assertSee('https://manage-subs.example.com/build/css/app.min.css', false)
            ->assertDontSee('http://manage-subs.example.com/build/', false);
    }
}
