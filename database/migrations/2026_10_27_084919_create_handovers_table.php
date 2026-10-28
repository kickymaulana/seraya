<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handovers', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->string('type');
            $table->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('DRAFT');
            $table->date('transaction_date');
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sender_confirmed_at')->nullable();
            $table->timestamp('receiver_confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handovers');
    }
};
