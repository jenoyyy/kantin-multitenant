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
    Schema::create('payment_events', function (Blueprint $table) {
        $table->id();
        $table->foreignId('payment_attempt_id')->constrained()->cascadeOnDelete();
        $table->string('event_type', 50); // misalnya: 'authorization', 'capture', 'refund'
        $table->json('payload'); // simpan detail event dari provider
        $table->timestamps(6);
    });
}

public function down(): void
{
    Schema::dropIfExists('payment_events');
}

};
