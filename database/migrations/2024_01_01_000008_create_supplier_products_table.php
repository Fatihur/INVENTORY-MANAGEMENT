<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->decimal('buy_price', 15, 2)->nullable();
            $table->integer('moq')->default(1);
            $table->integer('lead_time_days_override')->nullable();
            $table->string('supplier_sku')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'product_id']);
            $table->index(['product_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
