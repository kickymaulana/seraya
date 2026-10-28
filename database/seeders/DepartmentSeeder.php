<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'MOULD',
            'FILLING',
            'WASHING',
            'CUCI CELUP',
            'SPRAY ON HALUS',
            'OVEN',
            'ASAH / GRATING',
            'QC',
            'TEXTURE / SPK',
            'MOULD DESIGN',
            'QA',
            'FQC',
            'IT',
            'MTC',
            'GUDANG',
            'COMPOUND',
            'CASTING',
            'HRD',
            'SOLAR',
            'QS',
            'FINISH GOOD',
            'SANITARY',
            'CRUSHER',
            'PPIC',
            'RND',
            'ACCOUNTING',
            'GLAZE',
            'PURCHASING',
            'WATER TREATMENT PLANT',
            'PRODUKSI',
            'UMUM',
            'EXIM',
            'TAPAK',
            'PROJECT',
            'PACKING',
            'CNC',
            'HSE',
            'QC Supporting',
        ] as $name) {
            $department = [
                'name' => $name,
                'description' => null,
            ];
            Department::query()->updateOrCreate(
                ['name' => $department['name']],
                [...$department, 'is_active' => true],
            );
        }
    }
}
