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
        DB::table('product_options')->truncate();
        DB::table('product_option_values')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('variant_option_values')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get option types
        $colorTypeId = DB::table('product_option_types')->where('name', 'Color')->first()->id;
        $sizeTypeId = DB::table('product_option_types')->where('name', 'Size')->first()->id;
        $storageTypeId = DB::table('product_option_types')->where('name', 'Storage')->first()->id;

        // First Product (T-Shirt with Size and Color variants)
        $tshirt = Product::create([
            'name_en' => 'Cotton T-Shirt',
            'name_ar' => 'تي شيرت قطني',
            'description_en' => 'High quality cotton t-shirt',
            'description_ar' => 'تي شيرت قطني عالي الجودة',
            'price' => 100,
            'price_after_discount' => 90,
            'sku' => 'TSH-BASE',
            'quantity' => 0, // Total quantity will be sum of variants
            'is_active' => true,
            'has_variants' => true,
        ]);

        // Create Size Option
        $sizeOption = ProductOption::create([
            'product_id' => $tshirt->id,
            'product_option_type_id' => $sizeTypeId,
            'label' => 'Size',
            'order' => 1,
        ]);

        $sizes = ['S', 'M', 'L', 'XL'];
        $sizeValues = [];
        foreach ($sizes as $index => $size) {
            $sizeValues[$size] = ProductOptionValue::create([
                'product_option_id' => $sizeOption->id,
                'value' => $size,
                'order' => $index + 1,
            ]);
        }

        // Create Color Option
        $colorOption = ProductOption::create([
            'product_id' => $tshirt->id,
            'product_option_type_id' => $colorTypeId,
            'label' => 'Color',
            'order' => 2,
        ]);

        $colors = [
            ['name' => 'White', 'hex' => '#FFFFFF'],
            ['name' => 'Black', 'hex' => '#000000'],
            ['name' => 'Blue', 'hex' => '#0000FF']
        ];
        $colorValues = [];
        foreach ($colors as $index => $color) {
            $colorValues[$color['name']] = ProductOptionValue::create([
                'product_option_id' => $colorOption->id,
                'value' => $color['name'],
                'hex_code' => $color['hex'],
                'order' => $index + 1,
            ]);
        }

        // Create T-Shirt Variants
        foreach ($sizes as $size) {
            foreach ($colors as $color) {
                $variant = ProductVariant::create([
                    'product_id' => $tshirt->id,
                    'sku' => "TSH-{$size}-{$color['name']}",
                    'price' => 100,
                    'price_after_discount' => 90,
                    'quantity' => 10,
                    'barcode' => rand(100000000000, 999999999999),
                    'weight' => 0.2,
                    'is_active' => true,
                    'order' => 1,
                ]);

                // Link variant with option values
                VariantOptionValue::create([
                    'product_variant_id' => $variant->id,
                    'product_option_value_id' => $sizeValues[$size]->id,
                ]);

                VariantOptionValue::create([
                    'product_variant_id' => $variant->id,
                    'product_option_value_id' => $colorValues[$color['name']]->id,
                ]);
            }
        }

        // Second Product (Smartphone with Storage and Color variants)
        $smartphone = Product::create([
            'name_en' => 'Smart Phone X',
            'name_ar' => 'هاتف سمارت إكس',
            'description_en' => 'Latest smartphone with amazing features',
            'description_ar' => 'أحدث هاتف ذكي بميزات مذهلة',
            'price' => 2000,
            'price_after_discount' => 1800,
            'sku' => 'SPH-BASE',
            'quantity' => 0,
            'is_active' => true,
            'has_variants' => true,
        ]);

        // Create Storage Option
        $storageOption = ProductOption::create([
            'product_id' => $smartphone->id,
            'product_option_type_id' => $storageTypeId,
            'label' => 'Storage',
            'order' => 1,
        ]);

        $storages = ['128GB', '256GB', '512GB'];
        $storageValues = [];
        foreach ($storages as $index => $storage) {
            $storageValues[$storage] = ProductOptionValue::create([
                'product_option_id' => $storageOption->id,
                'value' => $storage,
                'order' => $index + 1,
            ]);
        }

        // Create Color Option for Smartphone
        $phoneColorOption = ProductOption::create([
            'product_id' => $smartphone->id,
            'product_option_type_id' => $colorTypeId,
            'label' => 'Color',
            'order' => 2,
        ]);

        $phoneColors = [
            ['name' => 'Silver', 'hex' => '#C0C0C0'],
            ['name' => 'Gold', 'hex' => '#FFD700'],
            ['name' => 'Black', 'hex' => '#000000']
        ];
        $phoneColorValues = [];
        foreach ($phoneColors as $index => $color) {
            $phoneColorValues[$color['name']] = ProductOptionValue::create([
                'product_option_id' => $phoneColorOption->id,
                'value' => $color['name'],
                'hex_code' => $color['hex'],
                'order' => $index + 1,
            ]);
        }

        // Create Smartphone Variants
        $basePrice = 2000;
        foreach ($storages as $storage) {
            foreach ($phoneColors as $color) {
                // Increase price for higher storage
                $variantPrice = $basePrice + (strpos($storage, '256') === 0 ? 200 : (strpos($storage, '512') === 0 ? 400 : 0));

                $variant = ProductVariant::create([
                    'product_id' => $smartphone->id,
                    'sku' => "SPH-{$storage}-{$color['name']}",
                    'price' => $variantPrice,
                    'price_after_discount' => $variantPrice * 0.9,
                    'quantity' => 5,
                    'barcode' => rand(100000000000, 999999999999),
                    'weight' => 0.3,
                    'is_active' => true,
                    'order' => 1,
                ]);

                // Link variant with option values
                VariantOptionValue::create([
                    'product_variant_id' => $variant->id,
                    'product_option_value_id' => $storageValues[$storage]->id,
                ]);

                VariantOptionValue::create([
                    'product_variant_id' => $variant->id,
                    'product_option_value_id' => $phoneColorValues[$color['name']]->id,
                ]);
            }
        }

        // Third Product (Simple product without variants)
        Product::create([
            'name_en' => 'Wireless Mouse',
            'name_ar' => 'ماوس لاسلكي',
            'description_en' => 'Ergonomic wireless mouse with long battery life',
            'description_ar' => 'ماوس لاسلكي مريح مع عمر بطارية طويل',
            'price' => 150,
            'price_after_discount' => 130,
            'sku' => 'WM-001',
            'quantity' => 50,
            'is_active' => true,
            'has_variants' => false,
        ]);
    }
}
