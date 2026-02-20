<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('serial_number')->unique();
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('available'); // available, assigned, sold, returned
            $table->foreignId('sales_order_id')->nullable()->constrained()->onDelete('set null');
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
    }
};
