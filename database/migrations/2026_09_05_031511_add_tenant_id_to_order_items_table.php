<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->after('tenant_order_id');
        });

        // Isi tenant_id dari tenant_orders untuk data yang sudah ada (portable, tanpa JOIN di UPDATE)
        DB::table('tenant_orders')->select('id', 'tenant_id')->orderBy('id')->chunk(200, function ($tenantOrders) {
            foreach ($tenantOrders as $tenantOrder) {
                DB::table('order_items')
                    ->where('tenant_order_id', $tenantOrder->id)
                    ->update(['tenant_id' => $tenantOrder->tenant_id]);
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign(['tenant_id', 'menu_id'])
                ->references(['tenant_id', 'id'])
                ->on('menus')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['tenant_id', 'menu_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
