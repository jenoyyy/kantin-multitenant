<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('bank_name', 100);
            $table->text('account_number_encrypted');
            $table->string('account_number_last4', 4);
            $table->string('account_holder_name', 120);
            $table->timestamps(6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
