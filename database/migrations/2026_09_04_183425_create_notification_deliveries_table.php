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
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 50); // misalnya: email, sms, push
            $table->string('recipient', 150); // alamat email, nomor hp, device token
            $table->string('message_type', 50); // jenis pesan, misalnya: order_update
            $table->json('payload'); // isi pesan
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamps(6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
