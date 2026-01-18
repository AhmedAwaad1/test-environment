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
        if (!Schema::hasTable('product_product_set_item')) {
            Schema::create('product_product_set_item', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_set_item_id')->constrained('product_set_items')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();
                
                // Ensure unique combination
                $table->unique(['product_set_item_id', 'product_id']);
            });
        }
        // If table already exists, we assume it was created manually or by another process
        // Foreign keys can be added manually if needed for data integrity
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_product_set_item');
    }
};
