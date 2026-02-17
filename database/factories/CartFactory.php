<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition()
    {
        return [
            'user_id'                    => User::factory(),
            'session_id'                 => null,
            'coupon_code'                => null,
            'discount_amount'            => 0,
            'total_price'                => $this->faker->randomFloat(2, 10, 500),
            'total_price_after_discount' => $this->faker->randomFloat(2, 10, 500),
            'currency_id'                => Currency::factory(),
        ];
    }
}
