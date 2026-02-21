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
        Schema::table('products', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('id');
            $table->decimal('cost_price', 15, 2)->default(0)->after('category');
            $table->decimal('selling_price', 15, 2)->default(0)->after('cost_price');
            $table->integer('max_stock')->nullable()->after('min_stock');
            $table->boolean('track_serial')->default(false)->after('track_batch');
        });

        \Illuminate\Support\Facades\DB::table('products')
            ->whereNull('code')
            ->update(['code' => \Illuminate\Support\Facades\DB::raw('sku')]);

        Schema::table('products', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'cost_price', 'selling_price', 'max_stock', 'track_serial']);
        });
    }
};
