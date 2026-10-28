<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('nik')->nullable()->unique()->after('id');
            $table->string('avatar_url')->nullable()->after('email');
            $table->boolean('active')->default(true)->after('avatar_url');
            $table->string('role')->default('viewer')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['nik']);
            $table->dropColumn(['nik', 'avatar_url', 'active']);
            $table->string('role')->default('viewer')->change();
        });
    }
};
