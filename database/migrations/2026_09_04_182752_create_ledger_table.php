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
    Schema::create('ledger', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
        $table->string('entry_type', 50); // misalnya: 'order_income', 'commission', 'refund'
        $table->unsignedBigInteger('amount');
        $table->json('metadata')->nullable(); // detail tambahan
        $table->timestamps(6);
    });
}

public function down(): void
{
    Schema::dropIfExists('ledger');
}

};
