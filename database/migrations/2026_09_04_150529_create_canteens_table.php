<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteens', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->enum('status', ['pending', 'active', 'suspended', 'inactive'])->default('pending');
            $table->timestamps(6);
            $table->softDeletes('deleted_at', 6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteens');
    }
};