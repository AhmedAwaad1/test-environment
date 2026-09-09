<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('erp_kind_code')->nullable()->after('sub_sub_category_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_code_index');
            $table->unique('code');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropIndex('sub_categories_code_index');
            $table->unique('code');
        });

        Schema::table('sub_sub_categories', function (Blueprint $table) {
            $table->dropIndex('sub_sub_categories_code_index');
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('sub_sub_categories', function (Blueprint $table) {
            $table->dropUnique('sub_sub_categories_code_unique');
            $table->index('code');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropUnique('sub_categories_code_unique');
            $table->index('code');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_code_unique');
            $table->index('code');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('erp_kind_code');
        });
    }
};
