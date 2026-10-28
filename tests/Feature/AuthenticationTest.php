<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        auth()->logout();

        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_page_is_the_sso_entry_point(): void
    {
        auth()->logout();

        $this->get('/login')->assertRedirect();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $this->actingAs($user);

        $this->post('/logout')->assertRedirect('/sso/login');
        $this->assertGuest();
    }
}
