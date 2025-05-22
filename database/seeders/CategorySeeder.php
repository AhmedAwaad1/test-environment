<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            [
                'name_en' => 'Mens fashion',
                'name_ar' => 'موضة الرجال',
                'slug' => 'mens-fashion',
                'order' => 1,
            ],
            [
                'name_en' => 'Accessories',
                'name_ar' => 'إكسسوارات',
                'slug' => 'accessories',
                'order' => 4,
            ],
            [
                'name_en' => 'Books',
                'name_ar' => 'كتب',
                'slug' => 'books',
                'order' => 3,
            ],
            [
                'name_en' => 'Furniture',
                'name_ar' => 'أثاث',
                'slug' => 'furniture',
                'order' => 2,
            ],
            
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
