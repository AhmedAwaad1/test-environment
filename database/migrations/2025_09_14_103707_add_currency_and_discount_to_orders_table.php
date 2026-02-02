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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'currency_id')) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0.00)->after('subtotal');
            }

            if (Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->change();
            }
            if (Schema::hasColumn('orders', 'shipping_price')) {
                $table->decimal('shipping_price', 10, 2)->change();
            }
            if (Schema::hasColumn('orders', 'total_price')) {
                $table->decimal('total_price', 10, 2)->change();
            }
        });

        $defaultCurrencyId = DB::table('currencies')->where('is_default', true)->value('id');
        if (!$defaultCurrencyId) {
            $defaultCurrencyId = DB::table('currencies')->min('id');
        }

        if ($defaultCurrencyId) {
            DB::table('orders')->whereNull('currency_id')->update(['currency_id' => $defaultCurrencyId]);
            DB::table('orders')->where('currency_id', 0)->update(['currency_id' => $defaultCurrencyId]);
        }

        Schema::table('orders', function (Blueprint $table) use ($defaultCurrencyId) {
            if ($defaultCurrencyId || DB::table('orders')->count() === 0) {
                $table->unsignedBigInteger('currency_id')->nullable(false)->change();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('currency_id')
                  ->references('id')->on('currencies')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'currency_id')) {
                try { $table->dropForeign(['currency_id']); } catch (\Throwable $e) {}
                $table->dropColumn('currency_id');
            }
            if (Schema::hasColumn('orders', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};
