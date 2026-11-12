<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('handover_items', function (Blueprint $table): void {
            $table->string('approval_status')->nullable()->after('notes');
            $table->text('rejection_reason')->nullable()->after('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('handover_items', function (Blueprint $table): void {
            $table->dropColumn(['approval_status', 'rejection_reason']);
        });
    }
};
