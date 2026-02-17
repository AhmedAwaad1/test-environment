<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition()
    {
        return [
            'name_en'      => $this->faker->country,
            'name_ar'      => $this->faker->country . ' (AR)',
            'code'         => $this->faker->unique()->countryCode,
            'phone_code'   => $this->faker->numerify('###'),
            'is_active'    => true,
        ];
    }
}
