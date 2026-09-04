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
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->string('actor_type', 50); // misalnya: 'user', 'system'
        $table->unsignedBigInteger('actor_id')->nullable(); // ID user/tenant terkait
        $table->string('action', 100); // misalnya: 'create_order', 'update_payment'
        $table->string('target_type', 50); // entitas yang diubah, misalnya: 'order', 'payment'
        $table->unsignedBigInteger('target_id')->nullable(); // ID entitas terkait
        $table->json('changes')->nullable(); // detail perubahan
        $table->timestamps(6);
    });
}

public function down(): void
{
    Schema::dropIfExists('audit_logs');
}

};
