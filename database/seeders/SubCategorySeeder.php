<?php

namespace Database\Seeders;

use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sub_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $sub_categories = [
            [
                'category_id' => 1,
                'name_en' => 'Jackets',
                'name_ar' => 'جاكيتات',
                'slug' => 'jackets',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'category_id' => 1,
                'name_en' => 'Shoes',
                'name_ar' => 'أحذية',
                'slug' => 'shoes',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'category_id' => 2,
                'name_en' => 'Gold',
                'name_ar' => 'ذهب',
                'slug' => 'gold',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'category_id' => 2,
                'name_en' => 'Silver',
                'name_ar' => 'فضة',
                'slug' => 'silver',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'category_id' => 2,
                'name_en' => 'Platinum',
                'name_ar' => 'بلاتين',
                'slug' => 'platinum',
                'order' => 3,
                'is_active' => true,
            ],
            [
            'category_id' => 1,
            'name_en' => 'T-Shirts',
            'name_ar' => 'تيشيرتات',
            'slug' => 'mens-tshirts',
            'order' => 4,
            'is_active' => true,
            ],
            [
                'category_id' => 2,
                'name_en' => 'Wallets',
                'name_ar' => 'محافظ',
                'slug' => 'leather-wallets',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'category_id' => 3,
                'name_en' => 'Desks',
                'name_ar' => 'مكاتب',
                'slug' => 'desks',
                'order' => 1,
                'is_active' => true,
            ],

        ];

        foreach ($sub_categories as $sub_category) {
            SubCategory::create($sub_category);
        }
    }
}
