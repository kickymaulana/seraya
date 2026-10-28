<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Factory as FactoryModel;
use App\Models\User;
use Database\Seeders\SerayaDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SerayaDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_idempotent_users_and_relationships(): void
    {
        $localUser = User::factory()->create([
            'email' => 'pegawai.sso@perusahaan.co.id',
            'name' => 'Pegawai SSO Lokal',
        ]);

        $this->seed(SerayaDemoSeeder::class);
        $this->seed(SerayaDemoSeeder::class);

        $demoUsers = User::query()->where('email', 'like', 'seraya.demo.%@demo.local')->get();

        $this->assertCount(30, $demoUsers);
        $this->assertSame(30, User::query()->where('email', 'like', 'seraya.demo.%@demo.local')->count());
        $this->assertSame(22, $demoUsers->where('role', 'viewer')->count());
        $this->assertSame(8, $demoUsers->whereIn('role', ['operator', 'admin'])->count());
        $this->assertTrue($demoUsers->every(fn (User $user): bool => $user->active));
        $this->assertModelExists($localUser);

        $this->assertSame(8, Department::query()->whereIn('name', [
            'PRODUKSI',
            'QC',
            'GUDANG',
            'HSE',
            'HRD',
            'IT',
            'PURCHASING',
            'PPIC',
        ])->count());
        $this->assertSame(3, FactoryModel::query()->whereIn('name', [
            'PABRIK CIKARANG',
            'PABRIK KARAWANG',
            'PABRIK SEMARANG',
        ])->count());
        $this->assertDatabaseHas('department_user', [
            'user_id' => $demoUsers->firstWhere('email', 'seraya.demo.01@demo.local')->id,
            'department_id' => Department::query()->where('name', 'IT')->value('id'),
        ]);
        $this->assertDatabaseHas('factory_user', [
            'user_id' => $demoUsers->firstWhere('email', 'seraya.demo.01@demo.local')->id,
            'factory_id' => FactoryModel::query()->where('name', 'PABRIK CIKARANG')->value('id'),
        ]);
    }
}
