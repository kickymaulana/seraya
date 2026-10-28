<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('factories')) {
            Schema::create('factories', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('positions') && ! Schema::hasColumn('positions', 'level')) {
            Schema::table('positions', function (Blueprint $table): void {
                $table->unsignedTinyInteger('level')->nullable()->after('name');
            });
        }
    }

    public function down(): void {}
};
