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
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('currency_id')
                  ->nullable()
                  ->constrained('currencies')
                  ->nullOnDelete();
        });

        $defaultId = DB::table('currencies')->where('is_default', 1)->value('id');
        if ($defaultId) {
            DB::table('carts')->whereNull('currency_id')->update(['currency_id' => $defaultId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });
    }
};
