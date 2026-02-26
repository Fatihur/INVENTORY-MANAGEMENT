<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('received_by')->constrained();
            $table->index(['warehouse_id', 'received_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropIndex(['warehouse_id', 'received_date']);
            $table->dropColumn('warehouse_id');
        });
    }
};
