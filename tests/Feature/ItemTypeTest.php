<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ItemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_types_can_be_listed_with_categories(): void
    {
        $category = Category::factory()->create(['name' => 'APD']);
        ItemType::factory()->create(['name' => 'MASKER', 'category_id' => $category->id]);

        $this->get('/item-types')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('ItemTypes/Index')
                ->where('itemTypes.0.name', 'MASKER')
                ->where('itemTypes.0.category.name', 'APD'));
    }

    public function test_item_type_can_be_created_with_category(): void
    {
        $category = Category::factory()->create(['name' => 'PERLENGKAPAN']);

        $this->post('/item-types', [
            'name' => 'KURSI',
            'category_id' => $category->id,
            'unit' => 'UNIT',
        ])->assertRedirect('/item-types');

        $this->assertDatabaseHas('item_types', [
            'name' => 'KURSI',
            'category_id' => $category->id,
            'unit' => 'UNIT',
        ]);
    }

    public function test_item_type_requires_an_active_category(): void
    {
        $category = Category::factory()->create(['is_active' => false]);

        $this->from('/item-types/create')
            ->post('/item-types', [
                'name' => 'KURSI',
                'category_id' => $category->id,
                'unit' => 'UNIT',
            ])
            ->assertRedirect('/item-types/create')
            ->assertSessionHasErrors('category_id');
    }
}
