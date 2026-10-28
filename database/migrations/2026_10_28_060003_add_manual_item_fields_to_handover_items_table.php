<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('handover_items', function (Blueprint $table): void {
            $table->dropForeign(['item_type_id']);
            $table->foreignId('item_type_id')->nullable()->change();
            $table->string('manual_name')->nullable()->after('item_type_id');
            $table->string('manual_unit')->nullable()->after('manual_name');
            $table->foreign('item_type_id')->references('id')->on('item_types')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('handover_items', function (Blueprint $table): void {
            $table->dropForeign(['item_type_id']);
            $table->dropColumn(['manual_name', 'manual_unit']);
            $table->foreignId('item_type_id')->nullable(false)->change();
            $table->foreign('item_type_id')->references('id')->on('item_types')->restrictOnDelete();
        });
    }
};
