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
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_order_id')->constrained()->cascadeOnDelete();
        $table->foreignId('menu_id')->constrained()->restrictOnDelete();
        $table->unsignedBigInteger('price_snapshot');
        $table->timestamps(6);
    });
}

public function down(): void
{
    Schema::dropIfExists('order_items');
}

};
