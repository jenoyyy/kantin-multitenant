<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps(6);

            $table->unique(['tenant_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};