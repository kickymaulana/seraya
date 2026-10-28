<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_cannot_manage_master_data(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);

        $this->actingAs($operator)->get('/master')->assertForbidden();
        $this->actingAs($operator)->get('/item-types')->assertForbidden();
    }

    public function test_admin_can_manage_master_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/master')->assertOk();
        $this->actingAs($admin)->get('/item-types')->assertOk();
    }
}
