<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition()
    {
        return [
            'country_id'   => Country::factory(),
            'name_en'      => $this->faker->city,
            'name_ar'      => $this->faker->city . ' (AR)',
            'is_active'    => true,
        ];
    }
}
