<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'APD', 'description' => 'Alat pelindung diri untuk kebutuhan operasional.'],
            ['name' => 'PC RAKITAN', 'description' => 'Komputer rakitan untuk kebutuhan operasional.'],
            ['name' => 'LAPTOP', 'description' => 'Komputer jinjing untuk kebutuhan operasional.'],
            ['name' => 'PRINTER', 'description' => 'Perangkat pencetak dokumen.'],
            ['name' => 'SERVER', 'description' => 'Perangkat server dan penyimpanan data.'],
            ['name' => 'MONITOR', 'description' => 'Layar monitor komputer.'],
        ] as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                [...$category, 'is_active' => true],
            );
        }
    }
}
