<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->string('reference', 100)->unique();
            $table->timestamps(6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
