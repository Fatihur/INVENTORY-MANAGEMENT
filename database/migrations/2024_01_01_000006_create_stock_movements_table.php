<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->enum('type', ['in', 'out', 'adjust', 'transfer_in', 'transfer_out']);
            $table->integer('qty');
            $table->integer('qty_before');
            $table->integer('qty_after');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('moved_at');
            $table->timestamps();

            $table->index(['product_id', 'type', 'moved_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('moved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
