<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = [
            [
                'name_en' => 'Skin Care',
                'name_ar' => 'العناية بالبشرة',
                'slug'    => 'skin-care',
            ],
            [
                'name_en' => 'Makeup',
                'name_ar' => 'مكياج',
                'slug'    => 'makeup',
            ],
        ];

        foreach ($categories as $cat) {
            $category = Category::create([
                'name_en'   => $cat['name_en'],
                'name_ar'   => $cat['name_ar'],
                'slug'      => $cat['slug'],
                'is_active' => true,
            ]);

            // SubCategories
            $subCategories = [
                ['en' => $cat['name_en'] . ' - Sub A', 'ar' => 'فرعي A - ' . $cat['name_ar']],
                ['en' => $cat['name_en'] . ' - Sub B', 'ar' => 'فرعي B - ' . $cat['name_ar']],
            ];

            foreach ($subCategories as $sub) {
                $subCategory = SubCategory::create([
                    'category_id' => $category->id,
                    'name_en'     => $sub['en'],
                    'name_ar'     => $sub['ar'],
                    'slug'        => strtolower(str_replace(' ', '-', $sub['en'])),
                    'is_active'   => true,
                ]);

                // Products
                $products = [
                    ['en' => $sub['en'] . ' Product 1', 'ar' => 'منتج 1 - ' . $sub['ar']],
                    ['en' => $sub['en'] . ' Product 2', 'ar' => 'منتج 2 - ' . $sub['ar']],
                ];

                foreach ($products as $prod) {
                    $product = Product::create([
                        'category_id'     => $category->id,
                        'sub_category_id' => $subCategory->id,
                        'name_en'         => $prod['en'],
                        'name_ar'         => $prod['ar'],
                        'description_en'  => 'A sample product for ' . $cat['name_en'],
                        'description_ar'  => 'منتج تجريبي لفئة ' . $cat['name_ar'],
                        'quantity'        => 50,
                        'is_active'       => true,
                        'has_variants'    => false,
                    ]);

                    // Base price in KWD (between 5 and 20 KWD)
                    $basePriceKWD = rand(5, 20);

                    // Gulf currencies only
                    $currencies = Currency::whereIn('name', ['KWD','SAR','AED','QAR','OMR','BHD'])->get();

                    foreach ($currencies as $currency) {
                        $price = match ($currency->name) {
                            'KWD' => $basePriceKWD,
                            'SAR' => $basePriceKWD * 12,  // تقريبًا
                            'AED' => $basePriceKWD * 12,
                            'QAR' => $basePriceKWD * 12,
                            'OMR' => $basePriceKWD * 0.8, // 1 KWD ≈ 0.8 OMR
                            'BHD' => $basePriceKWD * 1.2, // 1 KWD ≈ 1.2 BHD
                            default => $basePriceKWD,
                        };

                        $product->productPrices()->create([
                            'currency_id'         => $currency->id,
                            'price'               => $price,
                            'price_after_discount'=> $price * 0.9, // خصم 10%
                        ]);
                    }
                }
            }
        }
    }
}
