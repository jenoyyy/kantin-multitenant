<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('menu_id');
            $table->string('name', 100);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps(6);

            $table->unique(['tenant_id', 'id']);

            $table->foreign(['tenant_id', 'menu_id'])
                ->references(['tenant_id', 'id'])
                ->on('menus')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modifier_groups');
    }
};