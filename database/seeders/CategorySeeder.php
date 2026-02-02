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
                'name_en' => 'Meat',
                'name_ar' => 'لحوم',
                'slug' => 'meat',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name_en' => 'Chicken',
                'name_ar' => 'دواجن',
                'slug' => 'chicken',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name_en' => 'Shrimp & Seafood',
                'name_ar' => 'جمبري وسي فود',
                'slug' => 'shrimp-and-seafood',
                'order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
