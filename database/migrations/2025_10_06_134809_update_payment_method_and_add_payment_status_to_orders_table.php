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
        DB::statement("ALTER TABLE `orders` 
            MODIFY COLUMN `payment_method` ENUM('card','cod') NOT NULL");

        DB::statement("ALTER TABLE `orders`
            ADD COLUMN `payment_status` ENUM('pending','authorized','captured','failed','void','refunded') 
            NOT NULL DEFAULT 'pending' AFTER `payment_method`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `orders`
            MODIFY COLUMN `payment_method` ENUM('stripe','paypal','cod') NOT NULL");

        DB::statement("ALTER TABLE `orders`
            DROP COLUMN `payment_status`");
    }
};
