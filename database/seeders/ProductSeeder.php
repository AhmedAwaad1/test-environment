<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\SubCategory;
use App\Models\VariantOptionValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // // First Product (T-Shirt with Size and Color variants)
        $tshirt = Product::create([
            'name_en' => 'Cotton T-Shirt',
            'name_ar' => 'تي شيرت قطني',
            'description_en' => 'High quality cotton t-shirt',
            'description_ar' => 'تي شيرت قطني عالي الجودة',
            'sku' => 'TSH-BASE',
            'quantity' => 30, // Total quantity will be sum of variants
            'is_active' => true,
        ]);

        // // Second Product (Smartphone with Storage and Color variants)
        $smartphone = Product::create([
            'name_en' => 'Smart Phone X',
            'name_ar' => 'هاتف سمارت إكس',
            'description_en' => 'Latest smartphone with amazing features',
            'description_ar' => 'أحدث هاتف ذكي بميزات مذهلة',
            'sku' => 'SPH-BASE',
            'quantity' => 0,
            'is_active' => true,
        ]);

        // Third Product (Simple product without variants)
        Product::create([
            'name_en' => 'Wireless Mouse',
            'name_ar' => 'ماوس لاسلكي',
            'description_en' => 'Ergonomic wireless mouse with long battery life',
            'description_ar' => 'ماوس لاسلكي مريح مع عمر بطارية طويل',
            'sku' => 'WM-001',
            'quantity' => 50,
            'is_active' => true,
        ]);
    }
}
