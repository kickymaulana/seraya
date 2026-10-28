<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Administrator', 'email' => 'admin@hanyainputaja.test', 'role' => 'admin'],
            ['name' => 'Operator Aset', 'email' => 'operator@hanyainputaja.test', 'role' => 'operator'],
            ['name' => 'Viewer Aset', 'email' => 'viewer@hanyainputaja.test', 'role' => 'viewer'],
        ] as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => 'password'],
            );
        }
    }
}
