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
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->foreignId('completed_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('completed_by');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropConstrainedForeignId('completed_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn('cancelled_at');
        });
    }
};
