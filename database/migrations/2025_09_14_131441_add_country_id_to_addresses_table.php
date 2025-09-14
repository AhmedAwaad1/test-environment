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
        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'country_id')) {
                $table->foreignId('country_id')
                      ->nullable()
                      ->constrained('countries')
                      ->nullOnDelete();
            }
        });


        if (Schema::hasColumn('cities', 'country_id')) {
            try {
                DB::statement('
                    UPDATE addresses a
                    JOIN cities c ON a.city_id = c.id
                    SET a.country_id = c.country_id
                    WHERE a.city_id IS NOT NULL AND a.country_id IS NULL
                ');
            } catch (\Throwable $e) {
            }
        }

        try {
            DB::statement('
                UPDATE addresses a
                JOIN countries k ON k.country_code = "KW"
                SET a.country_id = k.id
                WHERE a.country_id IS NULL
            ');
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'country_id')) {
                $table->dropForeign(['country_id']);
                $table->dropColumn('country_id');
            }
        });
    }
};
