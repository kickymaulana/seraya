<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_departments_can_be_listed(): void
    {
        Department::factory()->create(['name' => 'IT']);

        $this->get('/departments')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Departments/Index')
            ->has('departments', 1));
    }

    public function test_department_can_be_created(): void
    {
        $this->post('/departments', ['name' => 'FQC', 'description' => 'Quality control'])
            ->assertRedirect('/departments');

        $this->assertDatabaseHas('departments', ['name' => 'FQC', 'is_active' => 1]);
    }

    public function test_department_name_must_be_unique(): void
    {
        Department::factory()->create(['name' => 'IT']);

        $this->from('/departments/create')->post('/departments', ['name' => 'IT'])
            ->assertRedirect('/departments/create')
            ->assertSessionHasErrors('name');
    }

    public function test_department_can_be_updated_and_deactivated(): void
    {
        $department = Department::factory()->create(['name' => 'IT']);

        $this->put('/departments/'.$department->id, ['name' => 'IT SUPPORT', 'is_active' => false])
            ->assertRedirect('/departments');

        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'IT SUPPORT', 'is_active' => 0]);
    }
}
