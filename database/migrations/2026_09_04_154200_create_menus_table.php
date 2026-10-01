<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('menu_category_id');
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_amount');
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps(6);

            $table->unique(['tenant_id', 'id']);

            $table->foreign(['tenant_id', 'menu_category_id'])
                ->references(['tenant_id', 'id'])
                ->on('menu_categories')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
