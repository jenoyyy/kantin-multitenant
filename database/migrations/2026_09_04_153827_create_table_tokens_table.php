<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dining_table_id')->constrained()->cascadeOnDelete();
            $table->char('token_hash', 64);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps(6);

            $table->unique('token_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_tokens');
    }
};
