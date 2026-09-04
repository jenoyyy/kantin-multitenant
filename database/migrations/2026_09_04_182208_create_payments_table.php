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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->cascadeOnDelete();
        $table->string('idempotency_key', 100)->unique(); // cegah duplikasi
        $table->unsignedBigInteger('amount');
        $table->enum('status', ['pending','success','failed'])->default('pending');
        $table->timestamps(6);
    });
}

public function down(): void
{
    Schema::dropIfExists('payments');
}

};
