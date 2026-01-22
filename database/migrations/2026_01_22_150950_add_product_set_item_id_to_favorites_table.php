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
        Schema::table('favorites', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('product_set_item_id')->nullable()->after('product_id')->constrained('product_set_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->change();
            $table->dropForeign(['product_set_item_id']);
            $table->dropColumn('product_set_item_id');
        });
    }
};
