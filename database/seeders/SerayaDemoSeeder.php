<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Factory as FactoryModel;
use App\Models\User;
use Illuminate\Database\Seeder;

class SerayaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $departments = collect([
            ['name' => 'PRODUKSI', 'description' => 'Departemen produksi dan operasional lini pabrik.'],
            ['name' => 'QC', 'description' => 'Pengendalian mutu bahan, proses, dan hasil produksi.'],
            ['name' => 'GUDANG', 'description' => 'Pengelolaan persediaan dan distribusi perlengkapan.'],
            ['name' => 'HSE', 'description' => 'Keselamatan, kesehatan kerja, dan lingkungan.'],
            ['name' => 'HRD', 'description' => 'Pengelolaan sumber daya manusia dan administrasi karyawan.'],
            ['name' => 'IT', 'description' => 'Dukungan sistem informasi dan perangkat kerja.'],
            ['name' => 'PURCHASING', 'description' => 'Pengadaan barang dan kebutuhan operasional.'],
            ['name' => 'PPIC', 'description' => 'Perencanaan produksi dan pengendalian persediaan.'],
        ])->mapWithKeys(function (array $department): array {
            $model = Department::query()->updateOrCreate(
                ['name' => $department['name']],
                [...$department, 'is_active' => true],
            );

            return [$department['name'] => $model];
        });

        $factories = collect([
            ['name' => 'PABRIK CIKARANG', 'description' => 'Fasilitas produksi Cikarang.'],
            ['name' => 'PABRIK KARAWANG', 'description' => 'Fasilitas produksi Karawang.'],
            ['name' => 'PABRIK SEMARANG', 'description' => 'Fasilitas produksi Semarang.'],
        ])->mapWithKeys(function (array $factory): array {
            $model = FactoryModel::query()->updateOrCreate(
                ['name' => $factory['name']],
                [...$factory, 'is_active' => true],
            );

            return [$factory['name'] => $model];
        });

        $users = [
            ['name' => 'Agus Setiawan', 'role' => 'admin', 'departments' => ['IT'], 'factories' => ['PABRIK CIKARANG', 'PABRIK KARAWANG', 'PABRIK SEMARANG']],
            ['name' => 'Dewi Lestari', 'role' => 'admin', 'departments' => ['HRD'], 'factories' => ['PABRIK CIKARANG', 'PABRIK KARAWANG']],
            ['name' => 'Budi Santoso', 'role' => 'operator', 'departments' => ['PRODUKSI'], 'factories' => ['PABRIK CIKARANG']],
            ['name' => 'Citra Wulandari', 'role' => 'operator', 'departments' => ['QC'], 'factories' => ['PABRIK KARAWANG']],
            ['name' => 'Eko Prasetyo', 'role' => 'operator', 'departments' => ['GUDANG'], 'factories' => ['PABRIK SEMARANG']],
            ['name' => 'Fitri Handayani', 'role' => 'operator', 'departments' => ['HSE'], 'factories' => ['PABRIK CIKARANG', 'PABRIK KARAWANG']],
            ['name' => 'Hendra Wijaya', 'role' => 'operator', 'departments' => ['PPIC'], 'factories' => ['PABRIK SEMARANG']],
            ['name' => 'Indah Permata', 'role' => 'operator', 'departments' => ['PURCHASING'], 'factories' => ['PABRIK CIKARANG']],
            ['name' => 'Joko Susilo', 'role' => 'viewer', 'departments' => ['PRODUKSI'], 'factories' => ['PABRIK CIKARANG']],
            ['name' => 'Kartika Sari', 'role' => 'viewer', 'departments' => ['PRODUKSI', 'QC'], 'factories' => ['PABRIK CIKARANG']],
            ['name' => 'Lukman Hakim', 'role' => 'viewer', 'departments' => ['GUDANG'], 'factories' => ['PABRIK KARAWANG']],
            ['name' => 'Maya Sari', 'role' => 'viewer', 'departments' => ['HSE'], 'factories' => ['PABRIK SEMARANG']],
            ['name' => 'Nanda Saputra', 'role' => 'viewer', 'departments' => ['HRD'], 'factories' => ['PABRIK CIKARANG']],
            ['name' => 'Oki Kurniawan', 'role' => 'viewer', 'departments' => ['IT'], 'factories' => ['PABRIK KARAWANG']],
            ['name' => 'Putri Maharani', 'role' => 'viewer', 'departments' => ['PURCHASING'], 'factories' => ['PABRIK SEMARANG']],
            ['name' => 'Rian Maulana', 'role' => 'viewer', 'departments' => ['PPIC'], 'factories' => ['PABRIK CIKARANG']],
            ['name' => 'Sari Amelia', 'role' => 'viewer', 'departments' => ['PRODUKSI'], 'factories' => ['PABRIK KARAWANG']],
            ['name' => 'Taufik Hidayat', 'role' => 'viewer', 'departments' => ['QC'], 'factories' => ['PABRIK SEMARANG']],
            ['name' => 'Umi Kalsum', 'role' => 'viewer', 'departments' => ['GUDANG'], 'factories' => ['PABRIK CIKARANG']],
            ['name' => 'Vina Oktaviani', 'role' => 'viewer', 'departments' => ['HSE'], 'factories' => ['PABRIK KARAWANG']],
            ['name' => 'Wahyu Nugroho', 'role' => 'viewer', 'departments' => ['HRD'], 'factories' => ['PABRIK SEMARANG']],
            ['name' => 'Yuni Astuti', 'role' => 'viewer', 'departments' => ['IT'], 'factories' => ['PABRIK CIKARANG']],
            ['name' => 'Zainal Arifin', 'role' => 'viewer', 'departments' => ['PURCHASING'], 'factories' => ['PABRIK KARAWANG']],
            ['name' => 'Andi Firmansyah', 'role' => 'viewer', 'departments' => ['PPIC'], 'factories' => ['PABRIK SEMARANG']],
            ['name' => 'Bella Novitasari', 'role' => 'viewer', 'departments' => ['PRODUKSI', 'HSE'], 'factories' => ['PABRIK CIKARANG', 'PABRIK SEMARANG']],
            ['name' => 'Cahyo Ramadhan', 'role' => 'viewer', 'departments' => ['QC'], 'factories' => ['PABRIK KARAWANG']],
            ['name' => 'Dian Anggraini', 'role' => 'viewer', 'departments' => ['GUDANG', 'PPIC'], 'factories' => ['PABRIK CIKARANG']],
            ['name' => 'Erwin Setiawan', 'role' => 'viewer', 'departments' => ['HSE'], 'factories' => ['PABRIK SEMARANG']],
            ['name' => 'Fajar Ramadhan', 'role' => 'viewer', 'departments' => ['HRD'], 'factories' => ['PABRIK KARAWANG']],
            ['name' => 'Galuh Puspita', 'role' => 'viewer', 'departments' => ['IT', 'PURCHASING'], 'factories' => ['PABRIK CIKARANG']],
        ];

        foreach ($users as $index => $userData) {
            $number = $index + 1;
            $user = User::query()->updateOrCreate(
                ['email' => sprintf('seraya.demo.%02d@demo.local', $number)],
                [
                    'nik' => sprintf('DEMO%04d', $number),
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'active' => true,
                    'password' => 'password',
                ],
            );

            $user->departments()->sync(collect($userData['departments'])->map(fn (string $name): int => $departments[$name]->id)->all());
            $user->factories()->sync(collect($userData['factories'])->map(fn (string $name): int => $factories[$name]->id)->all());
        }
    }
}
