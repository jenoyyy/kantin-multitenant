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
    Schema::create('outbox', function (Blueprint $table) {
        $table->id();
        $table->string('aggregate_type', 50); // misalnya: 'order', 'payment'
        $table->unsignedBigInteger('aggregate_id'); // ID dari entitas terkait
        $table->string('event_type', 50); // jenis event
        $table->json('payload'); // detail event
        $table->boolean('processed')->default(false); // sudah dipublish atau belum
        $table->timestamps(6);
    });
}

public function down(): void
{
    Schema::dropIfExists('outbox');
}

};
