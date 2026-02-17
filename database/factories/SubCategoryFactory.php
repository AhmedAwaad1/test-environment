<?php

namespace Database\Factories;

use App\Models\SubCategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubCategoryFactory extends Factory
{
    protected $model = SubCategory::class;

    public function definition()
    {
        $name = $this->faker->unique()->word;
        return [
            'category_id' => Category::factory(),
            'name_en'     => $name,
            'name_ar'     => $name . ' (AR)',
            'slug'        => Str::slug($name),
            'image'       => 'sub_categories/default.png',
            'order'       => $this->faker->numberBetween(1, 100),
            'is_active'   => true,
        ];
    }
}
