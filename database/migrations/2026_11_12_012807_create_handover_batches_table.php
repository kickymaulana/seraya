<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handover_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $table->string('status');
            $table->date('transaction_date');
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_batches');
    }
};
