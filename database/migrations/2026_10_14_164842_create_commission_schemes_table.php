<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_schemes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            // Disimpan sebagai pecahan: 0.1500 = 15%
            $table->decimal('commission_rate', 5, 4);
            // Interval setengah-terbuka [valid_from, valid_to); valid_to NULL = masih aktif
            $table->dateTime('valid_from', 6);
            $table->dateTime('valid_to', 6)->nullable();
            $table->timestamps(6);

            $table->index(['tenant_id', 'valid_from']);

            // Pengaman DB: hanya boleh ada satu skema aktif (valid_to NULL) per tenant
            $table->unsignedBigInteger('active_lock')
                ->virtualAs('case when `valid_to` is null then `tenant_id` else null end')
                ->nullable();
            $table->unique('active_lock', 'uq_commission_active_per_tenant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_schemes');
    }
};
