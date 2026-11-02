<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Factory;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_with_avatar_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'avatar_url' => 'https://example.test/avatar.jpg',
            'tanggal_masuk' => '2026-02-02',
        ]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Users/Index')
                ->where('users', fn ($users) => collect($users)->contains(fn (array $item): bool => $item['id'] === $user->id && $item['avatar_url'] === 'https://example.test/avatar.jpg')));
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_can_update_user_access_and_organization(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'viewer']);
        $position = Position::factory()->create();
        $department = Department::factory()->create();
        $factory = Factory::query()->create(['name' => 'Pabrik User', 'is_active' => true]);

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'role' => 'operator',
                'active' => false,
                'position_id' => $position->id,
                'department_ids' => [$department->id],
                'factory_ids' => [$factory->id],
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'operator',
            'active' => 0,
            'position_id' => $position->id,
        ]);
        $this->assertDatabaseHas('department_user', ['user_id' => $user->id, 'department_id' => $department->id]);
        $this->assertDatabaseHas('factory_user', ['user_id' => $user->id, 'factory_id' => $factory->id]);
    }
}
