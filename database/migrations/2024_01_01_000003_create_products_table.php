<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 20);
            $table->string('category', 50)->nullable();
            $table->integer('min_stock')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->integer('target_stock')->nullable();
            $table->integer('lead_time_days')->default(7);
            $table->boolean('track_batch')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sku', 'is_active']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
