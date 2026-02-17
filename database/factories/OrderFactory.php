<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Address;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id'         => User::factory(),
            'address_id'      => Address::factory(),
            'order_number'    => $this->faker->unique()->numerify('ORD-####-####'),
            'subtotal'        => $this->faker->randomFloat(2, 50, 500),
            'discount_amount' => $this->faker->randomFloat(2, 0, 50),
            'shipping_price'  => $this->faker->randomFloat(2, 5, 20),
            'total_price'     => $this->faker->randomFloat(2, 50, 600),
            'payment_method'  => 'card',
            'status'          => 'pending',
            'payment_status'  => 'pending',
            'currency_id'     => Currency::factory(),
        ];
    }
}
