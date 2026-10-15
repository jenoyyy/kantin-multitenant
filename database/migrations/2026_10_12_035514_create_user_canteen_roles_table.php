<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_canteen_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('canteen_id')->constrained()->restrictOnDelete();
            $table->enum('role', ['owner', 'manager', 'finance', 'viewer'])->default('viewer');
            $table->timestamps(6);
            $table->unique(['user_id', 'canteen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_canteen_roles');
    }
};