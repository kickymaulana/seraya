<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = collect(['admin', 'operator', 'viewer', 'hse']);

        $roles->each(fn (string $role) => DB::table('roles')->insertOrIgnore([
            'name' => $role,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $roleIds = DB::table('roles')->whereIn('name', $roles)->pluck('id', 'name');

        DB::table('users')->orderBy('id')->each(function (object $user) use ($roleIds): void {
            $roleId = $roleIds[$user->role] ?? $roleIds['viewer'];

            DB::table('model_has_roles')->insertOrIgnore([
                'role_id' => $roleId,
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        });
    }

    public function down(): void
    {
        DB::table('model_has_roles')->where('model_type', User::class)->delete();
        DB::table('roles')->whereIn('name', ['admin', 'operator', 'viewer', 'hse'])->delete();
    }
};
