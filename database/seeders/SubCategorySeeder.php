<?php

namespace Database\Seeders;

use App\Models\Category;
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

        $meat = Category::where('slug', 'meat')->first();
        $chicken = Category::where('slug', 'chicken')->first();
        $shrimp = Category::where('slug', 'shrimp-and-seafood')->first();

        $sub_categories = [
            // Meat
            [
                'category_id' => $meat->id,
                'name_en' => 'Steaks',
                'name_ar' => 'ستيك',
                'slug' => 'steaks',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'category_id' => $meat->id,
                'name_en' => 'Minced',
                'name_ar' => 'مفروم',
                'slug' => 'minced',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'category_id' => $meat->id,
                'name_en' => 'Roasts',
                'name_ar' => 'روستو/قطع للطبخ',
                'slug' => 'roasts',
                'order' => 3,
                'is_active' => true,
            ],
            // Chicken
            [
                'category_id' => $chicken->id,
                'name_en' => 'Whole Chicken',
                'name_ar' => 'دجاج كامل',
                'slug' => 'whole-chicken',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'category_id' => $chicken->id,
                'name_en' => 'Fillets & Breasts',
                'name_ar' => 'فيليه وصدور',
                'slug' => 'fillets-and-breasts',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'category_id' => $chicken->id,
                'name_en' => 'Appetizers',
                'name_ar' => 'مقبلات دجاج',
                'slug' => 'chicken-appetizers',
                'order' => 3,
                'is_active' => true,
            ],
            // Shrimp
            [
                'category_id' => $shrimp->id,
                'name_en' => 'Jumbo',
                'name_ar' => 'جامبو',
                'slug' => 'jumbo-shrimp',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'category_id' => $shrimp->id,
                'name_en' => 'Peeled',
                'name_ar' => 'مقشر',
                'slug' => 'peeled-shrimp',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'category_id' => $shrimp->id,
                'name_en' => 'Butterflied',
                'name_ar' => 'بترفلاي',
                'slug' => 'butterflied-shrimp',
                'order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($sub_categories as $sub_category) {
            SubCategory::create($sub_category);
        }
    }
}
