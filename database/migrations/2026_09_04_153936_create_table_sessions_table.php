<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dining_table_id')->constrained()->restrictOnDelete();
            $table->foreignId('table_token_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['active', 'closed', 'expired'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps(6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_sessions');
    }
};