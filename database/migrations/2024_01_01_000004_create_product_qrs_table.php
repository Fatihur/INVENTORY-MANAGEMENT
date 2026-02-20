<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_qrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('qr_code_value', 255)->unique();
            $table->enum('type', ['product', 'batch', 'location'])->default('product');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->timestamp('printed_at')->nullable();
            $table->integer('print_count')->default(0);
            $table->timestamps();

            $table->index(['qr_code_value', 'type']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_qrs');
    }
};
