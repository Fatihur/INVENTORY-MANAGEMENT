<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->integer('qty_on_hand')->default(0);
            $table->integer('qty_reserved')->default(0);
            $table->integer('qty_available')->virtualAs('qty_on_hand - qty_reserved');
            $table->decimal('avg_cost', 15, 2)->nullable();
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
            $table->index('qty_on_hand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
