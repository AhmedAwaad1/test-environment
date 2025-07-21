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
        Schema::table('cities', function (Blueprint $table) {
            $table->decimal('shipping_price', 10, 2)->nullable()->default(null)->change();
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->decimal('shipping_price', 10, 2)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->decimal('shipping_price', 10, 2)->default(0)->change();
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->decimal('shipping_price', 10, 2)->default(0)->change();
        });
    }
};
