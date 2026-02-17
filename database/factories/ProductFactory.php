<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name_en'         => $this->faker->word,
            'name_ar'         => $this->faker->word . ' (AR)',
            'description_en'  => $this->faker->paragraph,
            'description_ar'  => $this->faker->paragraph . ' (AR)',
            'category_id'     => Category::factory(),
            'sub_category_id' => SubCategory::factory(),
            'quantity'        => $this->faker->numberBetween(1, 100),
            'sku'             => $this->faker->unique()->bothify('SKU-####-????'),
            'is_active'       => true,
            'is_best_seller'  => $this->faker->boolean,
            'is_new_arrival'  => $this->faker->boolean,
            'has_variants'    => false,
        ];
    }
}
