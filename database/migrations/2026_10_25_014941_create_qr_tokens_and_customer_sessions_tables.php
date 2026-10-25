<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat token QR meja. Yang disimpan hanya hash (SHA-256, 32 byte).
        Schema::create('table_qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dining_table_id')->constrained('dining_tables')->cascadeOnDelete();
            $table->binary('token_hash', length: 32, fixed: true)->unique();
            $table->string('status', 20)->default('active');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['dining_table_id', 'status']);
        });

        // Sesi pelanggan anonim: ID acak (ULID), terikat ke kantin + meja.
        Schema::create('customer_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('canteen_id')->constrained('canteens')->restrictOnDelete();
            $table->foreignId('dining_table_id')->constrained('dining_tables')->restrictOnDelete();
            $table->foreignId('qr_token_id')->nullable()->constrained('table_qr_tokens')->nullOnDelete();
            $table->binary('session_token_hash', length: 32, fixed: true)->unique();
            $table->string('status', 20)->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_sessions');
        Schema::dropIfExists('table_qr_tokens');
    }
};
