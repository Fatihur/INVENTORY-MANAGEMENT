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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('approved_at');
            $table->timestamp('shipped_at')->nullable()->after('confirmed_at');
            $table->string('tracking_number')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'shipped_at', 'tracking_number', 'cancelled_at']);
        });
    }
};
