<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ItemType;
use Illuminate\Database\Seeder;

class ItemTypeSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];

        foreach ([
            ['name' => 'SEPATU SAFETY', 'category' => 'APD', 'unit' => 'PASANG'],
            ['name' => 'SEPATU BOOTS HIJAU', 'category' => 'APD', 'unit' => 'PASANG'],
            ['name' => 'CILEMEK', 'category' => 'APD', 'unit' => 'BUAH'],
            ['name' => 'HAIRNET', 'category' => 'APD', 'unit' => 'BUAH'],
            ['name' => 'MASKER', 'category' => 'APD', 'unit' => 'BUAH'],
            ['name' => 'SARUNG TANGAN', 'category' => 'APD', 'unit' => 'PASANG'],
            ['name' => 'KACA MATA', 'category' => 'APD', 'unit' => 'BUAH'],
        ] as $item) {
            $category = Category::query()->firstOrCreate(['name' => $item['category']], ['is_active' => true]);

            ItemType::query()->updateOrCreate(
                ['name' => $item['name']],
                ['category_id' => $category->id, 'unit' => $item['unit'], 'is_active' => true],
            );
        }
    }
}
