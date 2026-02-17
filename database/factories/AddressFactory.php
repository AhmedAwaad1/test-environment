<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition()
    {
        return [
            'user_id'      => User::factory(),
            'country_id'   => Country::factory(),
            'city_id'      => City::factory(),
            'district_id'  => null,
            'address'      => $this->faker->address,
            'phone'        => $this->faker->phoneNumber,
            'first_name'   => $this->faker->firstName,
            'last_name'    => $this->faker->lastName,
            'email'        => $this->faker->email,
            'is_default'   => false,
        ];
    }
}
