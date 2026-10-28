<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_can_be_listed(): void
    {
        Category::factory()->create(['name' => 'PC RAKITAN']);

        $response = $this->get('/categories');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Categories/Index')
            ->has('categories', 1)
        );
    }

    public function test_category_can_be_created(): void
    {
        $response = $this->post('/categories', [
            'name' => 'SERVER',
            'description' => 'Server backup',
        ]);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', [
            'name' => 'SERVER',
            'description' => 'SERVER BACKUP',
            'is_active' => 1,
        ]);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'MONITOR']);

        $response = $this->from('/categories/create')->post('/categories', [
            'name' => 'MONITOR',
        ]);

        $response->assertRedirect('/categories/create');
        $response->assertSessionHasErrors('name');
    }

    public function test_category_can_be_updated_and_deactivated(): void
    {
        $category = Category::factory()->create(['name' => 'LAPTOP']);

        $response = $this->put('/categories/'.$category->id, [
            'name' => 'LAPTOP KANTOR',
            'description' => 'Perangkat kerja',
            'is_active' => false,
        ]);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'LAPTOP KANTOR',
            'is_active' => 0,
        ]);
    }
}
