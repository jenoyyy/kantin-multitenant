<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('payment_attempts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
        $table->string('provider', 50); // misalnya: Midtrans, Xendit
        $table->string('attempt_reference', 100); // ID unik dari provider
        $table->enum('status', ['pending','success','failed'])->default('pending');
        $table->timestamps(6);
    });
}

public function down(): void
{
    Schema::dropIfExists('payment_attempts');
}

};
