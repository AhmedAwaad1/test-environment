<?php

namespace Database\Factories;

use App\Models\ProductPrice;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition()
    {
        $price = $this->faker->randomFloat(2, 10, 1000);
        return [
            'product_id'           => Product::factory(),
            'currency_id'          => Currency::factory(),
            'price'                => $price,
            'price_after_discount' => $this->faker->boolean(30) ? $price * 0.8 : null,
        ];
    }
}
