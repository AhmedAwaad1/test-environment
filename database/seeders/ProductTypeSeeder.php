<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            [
                'name_en' => 'Jackets',
                'name_ar' => 'جاكيتات',
                'slug' => 'jackets',
            ],
            [
                'name_en' => 'Shoes',
                'name_ar' => 'أحذية',
                'slug' => 'shoes',
            ],
            [
                'name_en' => 'T-Shirts',
                'name_ar' => 'تيشيرتات',
                'slug' => 't-shirts',
            ],
        ];

        foreach ($categories as $category) {
            ProductType::create($category);
        }
    }
}
