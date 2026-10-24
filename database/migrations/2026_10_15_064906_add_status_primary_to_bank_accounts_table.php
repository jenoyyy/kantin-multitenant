<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table): void {
            $table->string('status', 20)->default('unverified')->after('account_holder_name');
            $table->boolean('is_primary')->default(false)->after('status');

            // Pengaman DB: maksimal satu rekening utama per tenant
            $table->unsignedBigInteger('primary_lock')
                ->virtualAs('case when `is_primary` = 1 then `tenant_id` else null end')
                ->nullable();
            $table->unique('primary_lock', 'uq_bank_primary_per_tenant');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table): void {
            $table->dropUnique('uq_bank_primary_per_tenant');
            $table->dropColumn(['primary_lock', 'is_primary', 'status']);
        });
    }
};
