<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('order_items', 'total') && !Schema::hasColumn('order_items', 'total_price')) {
            try {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->renameColumn('total', 'total_price'); // يحتاج doctrine/dbal
                });
            } catch (\Throwable $e) {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->decimal('total_price', 10, 2)->nullable();
                });
                DB::table('order_items')->update(['total_price' => DB::raw('COALESCE(`total`, 0)')]);
                try {
                    Schema::table('order_items', function (Blueprint $table) {
                        $table->dropColumn('total');
                    });
                } catch (\Throwable $e2) {
                }
            }
        }
        if (Schema::hasColumn('order_items', 'total') && Schema::hasColumn('order_items', 'total_price')) {
            try {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->dropColumn('total');
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasColumn('order_items', 'price') && !Schema::hasColumn('order_items', 'unit_price')) {
            try {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->renameColumn('price', 'unit_price'); // يحتاج doctrine/dbal
                });
            } catch (\Throwable $e) {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->decimal('unit_price', 10, 2)->nullable();
                });
                DB::table('order_items')
                  ->whereNull('unit_price')
                  ->update(['unit_price' => DB::raw('price')]);
                try {
                    Schema::table('order_items', function (Blueprint $table) {
                        if (Schema::hasColumn('order_items', 'price')) {
                            $table->dropColumn('price');
                        }
                    });
                } catch (\Throwable $e2) {
                }
            }
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'unit_price_after_discount')) {
                $table->decimal('unit_price_after_discount', 10, 2)->nullable();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'total_price')) {
                $table->decimal('total_price', 10, 2)->nullable();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_price_id')) {
                $table->foreignId('product_price_id')
                      ->nullable()
                      ->constrained('product_prices')
                      ->cascadeOnUpdate()
                      ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            try {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->unsignedBigInteger('product_variant_id')->nullable()->change();
                });
            } catch (\Throwable $e) {
            }
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'unit_price')) {
                try {
                    $table->decimal('unit_price', 10, 2)->change();
                } catch (\Throwable $e) {
                }
            }
            if (Schema::hasColumn('order_items', 'total_price')) {
                try {
                    $table->decimal('total_price', 10, 2)->nullable()->change();
                } catch (\Throwable $e) {
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_price_id')) {
                try {
                    $table->dropForeign(['product_price_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('product_price_id');
            }
            if (Schema::hasColumn('order_items', 'total_price')) {
                $table->dropColumn('total_price');
            }
            if (Schema::hasColumn('order_items', 'unit_price_after_discount')) {
                $table->dropColumn('unit_price_after_discount');
            }
            if (Schema::hasColumn('order_items', 'unit_price') && !Schema::hasColumn('order_items', 'price')) {
                try {
                    $table->renameColumn('unit_price', 'price');
                } catch (\Throwable $e) {
                }
            }
        });
    }
};
