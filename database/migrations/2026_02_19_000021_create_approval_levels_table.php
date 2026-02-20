<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model_type'); // e.g., PurchaseOrder, SalesOrder
            $table->integer('level_order')->default(1);
            $table->json('conditions')->nullable(); // JSON conditions for approval
            $table->unsignedBigInteger('role_id')->nullable(); // Removed foreign key constraint
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_levels');
    }
};
