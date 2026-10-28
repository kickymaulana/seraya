<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsoAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        auth()->logout();
    }

    public function test_login_redirects_to_once_login_authorization(): void
    {
        config()->set('services.sso.client_id', 'test-client');

        $response = $this->get(route('sso.login'));

        $response->assertRedirectContains('/oauth/authorize');
        $response->assertRedirectContains('client_id=test-client');
        $response->assertRedirectContains('redirect_uri='.urlencode(route('sso.callback')));
    }

    public function test_active_sso_user_is_created_and_authenticated_without_pending_gate(): void
    {
        config()->set('services.sso.base_url', 'https://sso.example.test');
        config()->set('services.sso.client_id', 'test-client');

        Http::fake([
            'https://sso.example.test/oauth/token' => Http::response(['access_token' => 'test-token']),
            'https://sso.example.test/api/user' => Http::response([
                'nik' => 'D260065',
                'name' => 'Kicky Maulana',
                'email' => 'kicky@example.test',
                'avatar_url' => 'https://sso.example.test/avatar.jpg',
                'active' => true,
            ]),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'test-code']));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'nik' => 'D260065',
            'name' => 'Kicky Maulana',
            'email' => 'kicky@example.test',
            'active' => 1,
        ]);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_existing_sso_user_keeps_local_organization_fields(): void
    {
        $user = User::factory()->create([
            'nik' => 'D260065',
            'name' => 'Nama Lokal',
            'email' => 'lokal@example.test',

        ]);

        config()->set('services.sso.base_url', 'https://sso.example.test');
        config()->set('services.sso.client_id', 'test-client');

        Http::fake([
            'https://sso.example.test/oauth/token' => Http::response(['access_token' => 'test-token']),
            'https://sso.example.test/api/user' => Http::response([
                'nik' => 'D260065',
                'name' => 'Nama dari SSO',
                'email' => 'sso@example.test',
                'active' => true,
            ]),
        ]);

        $this->get(route('sso.callback', ['code' => 'test-code']))->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nik' => 'D260065',
            'name' => 'Nama dari SSO',
            'email' => 'sso@example.test',
        ]);
    }

    public function test_inactive_sso_user_cannot_login(): void
    {
        config()->set('services.sso.base_url', 'https://sso.example.test');
        config()->set('services.sso.client_id', 'test-client');

        Http::fake([
            'https://sso.example.test/oauth/token' => Http::response(['access_token' => 'test-token']),
            'https://sso.example.test/api/user' => Http::response([
                'nik' => 'D260065',
                'name' => 'Nonaktif',
                'active' => false,
            ]),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'test-code']));

        $response->assertRedirect(route('sso.login'));
        $response->assertSessionHasErrors('sso');
        $this->assertGuest();
    }
}
