<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ItemType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemType>
 */
class ItemTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'category_id' => Category::factory(),
            'unit' => 'UNIT',
            'is_active' => true,
        ];
    }
}
