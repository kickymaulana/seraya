<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Factory;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organization = json_decode(File::get(database_path('data/simano-organization.json')), true, 512, JSON_THROW_ON_ERROR);

        foreach ($organization['departments'] as $name) {
            Department::query()->updateOrCreate(
                ['name' => $name],
                ['description' => null, 'is_active' => true],
            );
        }

        foreach ($organization['factories'] as $name) {
            Factory::query()->updateOrCreate(
                ['name' => $name],
                ['description' => null, 'is_active' => true],
            );
        }

        foreach ($organization['positions'] as $position) {
            Position::query()->updateOrCreate(
                ['name' => $position['name']],
                ['description' => null, 'level' => $position['level'], 'is_active' => true],
            );
        }
    }
}
