<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('batch_number')->unique();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('initial_qty')->default(0);
            $table->integer('remaining_qty')->default(0);
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
