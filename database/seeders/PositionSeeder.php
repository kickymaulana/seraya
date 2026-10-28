<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Manager', 'Supervisor', 'Staff', 'Operator'] as $name) {
            Position::query()->updateOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
