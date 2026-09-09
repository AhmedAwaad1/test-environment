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
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('code')->nullable()->index()->after('id');
            $table->text('notes')->nullable()->after('is_active');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('code')->nullable()->index()->after('id');
            $table->unsignedBigInteger('ucode1')->nullable()->after('code');
            $table->text('notes')->nullable()->after('is_active');
        });

        Schema::create('sub_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->unsignedBigInteger('code')->nullable()->index();
            $table->unsignedBigInteger('ucode1')->nullable();
            $table->unsignedBigInteger('ucode2')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_ar');
            $table->string('slug')->nullable();
            $table->string('image')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('sub_sub_category_id')->nullable()->after('sub_category_id')->constrained('sub_sub_categories')->nullOnDelete();
            $table->unsignedBigInteger('erp_group_code')->nullable()->after('sub_sub_category_id');
            $table->unsignedBigInteger('erp_group2_code')->nullable()->after('erp_group_code');
            $table->unsignedBigInteger('erp_group3_code')->nullable()->after('erp_group2_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['sub_sub_category_id']);
            $table->dropColumn(['sub_sub_category_id', 'erp_group_code', 'erp_group2_code', 'erp_group3_code']);
        });

        Schema::dropIfExists('sub_sub_categories');

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn(['code', 'ucode1', 'notes']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['code', 'notes']);
        });
    }
};
