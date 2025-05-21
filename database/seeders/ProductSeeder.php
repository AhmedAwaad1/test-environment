<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products')->truncate();
        DB::table('product_variants')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $productsData = [
            [
                'name_en' => 'North Face Jacket',
                'name_ar' => 'جاكيت نورث فيس',
                'description_en' => 'Warm winter jacket',
                'description_ar' => 'جاكيت شتوي دافي',
                'price' => 1200,
                'price_after_discount' => 1000,
                'product_type_id' => 1,
                'category_id' => 1,
                'variants' => [
                    [
                        'color_id' => 1,
                        'size_id' => 1,
                        'sku' => 'NF-JACKET-001',
                        'price' => 1200,
                        'price_after_discount' => 1000,
                        'quantity' => 10,
                        'is_active' => true,
                    ],
                    [
                        'color_id' => 2,
                        'size_id' => 2,
                        'sku' => 'NF-JACKET-002',
                        'price' => 1200,
                        'price_after_discount' => 1000,
                        'quantity' => 5,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name_en' => 'Zara T-Shirt',
                'name_ar' => 'تيشيرت زارا',
                'description_en' => 'Casual summer t-shirt',
                'description_ar' => 'تيشيرت صيفي كاجوال',
                'price' => 400,
                'price_after_discount' => 350,
                'product_type_id' => 2,
                'category_id' => 2,
                'variant' => [
                    'color_id' => 2,
                    'size_id' => 2,
                    'sku' => 'ZARA-TSHIRT-001',
                    'price' => 400,
                    'price_after_discount' => 350,
                    'quantity' => 15,
                    'is_active' => true,
                ],
            ],
            [
                'name_en' => 'H&M Short',
                'name_ar' => 'شورت H&M',
                'description_en' => 'Comfortable cotton short',
                'description_ar' => 'شورت قطني مريح',
                'price' => 300,
                'price_after_discount' => 250,
                'product_type_id' => 3,
                'category_id' => 3,
                'variant' => [
                    'color_id' => 3,
                    'size_id' => 3,
                    'sku' => 'HM-SHORT-001',
                    'price' => 300,
                    'price_after_discount' => 250,
                    'quantity' => 20,
                    'is_active' => true,
                ],
            ],
        ];

        foreach ($productsData as $productData) {
            $variants = $productData['variants'] ?? null;
            $variant = $productData['variant'] ?? null;

            unset($productData['variants'], $productData['variant']);

            $product = Product::create($productData);

            if ($variants) {
                foreach ($variants as $v) {
                    ProductVariant::create(array_merge($v, ['product_id' => $product->id]));
                }
            } elseif ($variant) {
                ProductVariant::create(array_merge($variant, ['product_id' => $product->id]));
            }
        }
    }
}
