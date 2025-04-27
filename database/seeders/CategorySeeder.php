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
                'name_en' => 'Men',
                'name_ar' => 'رجال',
                'slug' => 'men',
            ],
            [
                'name_en' => 'Women',
                'name_ar' => 'نساء',
                'slug' => 'women',
            ],
            [
                'name_en' => 'kids',
                'name_ar' => 'أطفال',
                'slug' => 'kids',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
