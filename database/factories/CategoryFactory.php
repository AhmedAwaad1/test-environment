<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        $name = $this->faker->unique()->word;
        return [
            'name_en'   => $name,
            'name_ar'   => $name . ' (AR)',
            'slug'      => Str::slug($name),
            'image'     => 'categories/default.png',
            'order'     => $this->faker->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
