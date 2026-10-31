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
        config()->set('services.hrd.employee_url', 'https://hrd.example.test/get_karyawan.php');

        Http::fake([
            'https://sso.example.test/oauth/token' => Http::response(['access_token' => 'test-token']),
            'https://sso.example.test/api/user' => Http::response([
                'nik' => 'D260065',
                'name' => 'Kicky Maulana',
                'email' => 'kicky@example.test',
                'avatar_url' => 'https://sso.example.test/avatar.jpg',
                'active' => true,
            ]),
            'https://hrd.example.test/get_karyawan.php?nik_karyawan=D260065' => Http::response([
                'status' => true,
                'message' => 'Data karyawan ditemukan',
                'data' => [
                    'nik_karyawan' => 'D260065',
                    'nama_lengkap' => 'Kicky Maulana',
                    'tanggal_masuk' => '2026-02-02',
                ],
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
        config()->set('services.hrd.employee_url', 'https://hrd.example.test/get_karyawan.php');

        Http::fake([
            'https://sso.example.test/oauth/token' => Http::response(['access_token' => 'test-token']),
            'https://sso.example.test/api/user' => Http::response([
                'nik' => 'D260065',
                'name' => 'Nama dari SSO',
                'email' => 'sso@example.test',
                'active' => true,
            ]),
            'https://hrd.example.test/get_karyawan.php?nik_karyawan=D260065' => Http::response([
                'status' => true,
                'message' => 'Data karyawan ditemukan',
                'data' => [
                    'nik_karyawan' => 'D260065',
                    'nama_lengkap' => 'Kicky Maulana',
                    'tanggal_masuk' => '2026-02-02',
                ],
            ]),
        ]);

        $this->get(route('sso.callback', ['code' => 'test-code']))->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nik' => 'D260065',
            'name' => 'Kicky Maulana',
            'email' => 'sso@example.test',
            'tanggal_masuk' => '2026-02-02 00:00:00',
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

    public function test_user_with_unknown_hrd_nik_cannot_login_or_be_created(): void
    {
        config()->set('services.sso.base_url', 'https://sso.example.test');
        config()->set('services.sso.client_id', 'test-client');
        config()->set('services.hrd.employee_url', 'https://hrd.example.test/get_karyawan.php');

        Http::fake([
            'https://sso.example.test/oauth/token' => Http::response(['access_token' => 'test-token']),
            'https://sso.example.test/api/user' => Http::response([
                'nik' => 'D260065',
                'name' => 'Kicky Maulana',
                'active' => true,
            ]),
            'https://hrd.example.test/get_karyawan.php?nik_karyawan=D260065' => Http::response([
                'status' => false,
                'message' => 'NIK karyawan tidak ditemukan',
                'data' => null,
            ]),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'test-code']));

        $response->assertRedirect(route('sso.login'));
        $response->assertSessionHasErrors('sso', 'NIK karyawan tidak ditemukan');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['nik' => 'D260065']);
    }

    public function test_hrd_connection_failure_rejects_login(): void
    {
        config()->set('services.sso.base_url', 'https://sso.example.test');
        config()->set('services.sso.client_id', 'test-client');
        config()->set('services.hrd.employee_url', 'https://hrd.example.test/get_karyawan.php');

        Http::fake([
            'https://sso.example.test/oauth/token' => Http::response(['access_token' => 'test-token']),
            'https://sso.example.test/api/user' => Http::response([
                'nik' => 'D260065',
                'name' => 'Kicky Maulana',
                'active' => true,
            ]),
            'https://hrd.example.test/get_karyawan.php?nik_karyawan=D260065' => Http::failedConnection(),
        ]);

        $response = $this->get(route('sso.callback', ['code' => 'test-code']));

        $response->assertRedirect(route('sso.login'));
        $response->assertSessionHasErrors('sso');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['nik' => 'D260065']);
    }
}
