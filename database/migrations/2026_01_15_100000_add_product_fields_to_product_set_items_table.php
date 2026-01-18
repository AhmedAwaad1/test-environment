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
        Schema::table('product_set_items', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('product_id');
            $table->integer('quantity')->default(0)->after('sku');
            $table->boolean('is_active')->default(true)->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_set_items', function (Blueprint $table) {
            $table->dropColumn(['sku', 'quantity', 'is_active']);
        });
    }
};

