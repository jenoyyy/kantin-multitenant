<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dining_tables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('canteen_id')->constrained()->restrictOnDelete();
            $table->string('label', 20);
            $table->enum('status', ['available', 'occupied', 'inactive'])->default('available');
            $table->timestamps(6);

            $table->unique(['canteen_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dining_tables');
    }
};