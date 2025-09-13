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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_price_id')->nullable()->after('product_variant_id');
            $table->unsignedBigInteger('currency_id')->nullable()->after('product_price_id');
            $table->decimal('unit_price', 10, 2)->nullable()->after('currency_id');
            $table->decimal('unit_price_after_discount', 10, 2)->nullable()->after('unit_price');

            $table->foreign('product_price_id')->references('id')->on('product_prices')->nullOnDelete();
            $table->foreign('currency_id')->references('id')->on('currencies')->nullOnDelete();

            $table->index(['product_price_id']);
            $table->index(['currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_price_id']);
            $table->dropForeign(['currency_id']);
            $table->dropIndex(['product_price_id']);
            $table->dropIndex(['currency_id']);
            $table->dropColumn(['product_price_id','currency_id','unit_price','unit_price_after_discount']);
        });
    }
};
