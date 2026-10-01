<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->bigInteger('amount')->default(0);
            $table->timestamps(6);

            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
