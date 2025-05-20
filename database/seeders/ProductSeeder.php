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
                        'sku' => 'NF-JACKET-001',
                        'price' => 1200,
                        'price_after_discount' => 1000,
                        'is_active' => true,
                        'variant_sizes' => [
                            ['size_id' => 1, 'quantity' => 5],
                            ['size_id' => 2, 'quantity' => 10],
                            ['size_id' => 3, 'quantity' => 8],
                        ],
                    ],
                    [
                        'color_id' => 2,
                        'sku' => 'NF-JACKET-002',
                        'price' => 1200,
                        'price_after_discount' => 1000,
                        'is_active' => true,
                        'variant_sizes' => [
                            ['size_id' => 1, 'quantity' => 3],
                            ['size_id' => 2, 'quantity' => 7],
                            ['size_id' => 3, 'quantity' => 6],
                        ],
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
                    'sku' => 'ZARA-TSHIRT-001',
                    'price' => 400,
                    'price_after_discount' => 350,
                    'is_active' => true,
                    'variant_sizes' => [
                        ['size_id' => 1, 'quantity' => 10],
                        ['size_id' => 2, 'quantity' => 5],
                        ['size_id' => 3, 'quantity' => 8],
                        ['size_id' => 4, 'quantity' => 12],
                    ]
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
                    'sku' => 'HM-SHORT-001',
                    'price' => 300,
                    'price_after_discount' => 250,
                    'is_active' => true,
                    'variant_sizes' => [
                        ['size_id' => 2, 'quantity' => 7],
                        ['size_id' => 3, 'quantity' => 15],
                    ],
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
                    $variantSizes = $v['variant_sizes'] ?? [];
                    unset($v['variant_sizes']);

                    $productVariant = ProductVariant::create(array_merge($v, ['product_id' => $product->id]));

                    foreach ($variantSizes as $size) {
                        $productVariant->variantSizes()->create($size);
                    }
                }
            } elseif ($variant) {
                $variantSizes = $variant['variant_sizes'] ?? [];
                unset($variant['variant_sizes']);

                $productVariant = ProductVariant::create(array_merge($variant, ['product_id' => $product->id]));

                foreach ($variantSizes as $size) {
                    $productVariant->variantSizes()->create($size);
                }
            }
        }
    }
}
