<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handover_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('handover_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_type_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('condition')->default('BAIK');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_items');
    }
};
