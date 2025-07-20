<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing foreign keys using raw SQL (more reliable)
        try {
            DB::statement('ALTER TABLE addresses DROP FOREIGN KEY addresses_city_id_foreign');
        } catch (\Throwable $e) {
            // Key may not exist; safe to ignore
        }

        try {
            DB::statement('ALTER TABLE addresses DROP FOREIGN KEY addresses_district_id_foreign');
        } catch (\Throwable $e) {
            // Key may not exist; safe to ignore
        }

        // Modify columns to be nullable and recreate foreign keys with nullOnDelete
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable()->change();
            $table->unsignedBigInteger('district_id')->nullable()->change();

            $table->foreign('city_id')
                  ->references('id')->on('cities')
                  ->nullOnDelete();

            $table->foreign('district_id')
                  ->references('id')->on('districts')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['district_id']);

            $table->unsignedBigInteger('city_id')->nullable(false)->change();
            $table->unsignedBigInteger('district_id')->nullable(false)->change();

            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
            $table->foreign('district_id')->references('id')->on('districts')->cascadeOnDelete();
        });
    }
};
