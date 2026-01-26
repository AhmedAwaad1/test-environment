<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\District;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Country (Fallback level)
        $egypt = Country::updateOrCreate(
            ['name_en' => 'Egypt'],
            [
                'country_code' => 'EG',
                'name_ar' => 'مصر',
                'shipping_price' => 50.00,
            ]
        );

        // 2. Create Cities (Mid-level hierarchy)
        $cairo = City::updateOrCreate(
            ['name_en' => 'Cairo', 'country_id' => $egypt->id],
            [
                'name_ar' => 'القاهرة',
                'shipping_price' => 30.00,
            ]
        );

        $alex = City::updateOrCreate(
            ['name_en' => 'Alexandria', 'country_id' => $egypt->id],
            [
                'name_ar' => 'الإسكندرية',
                'shipping_price' => 40.00,
            ]
        );

        $giza = City::updateOrCreate(
            ['name_en' => 'Giza', 'country_id' => $egypt->id],
            [
                'name_ar' => 'الجيزة',
                'shipping_price' => 35.00,
            ]
        );

        // 3. Create Districts (Top-level hierarchy)
        $districts = [
            [
                'city_id' => $cairo->id,
                'name_en' => 'Maadi',
                'name_ar' => 'المعادي',
                'shipping_price' => 15.00,
            ],
            [
                'city_id' => $cairo->id,
                'name_en' => 'Zamalek',
                'name_ar' => 'الزمالك',
                'shipping_price' => 20.00,
            ],
            [
                'city_id' => $alex->id,
                'name_en' => 'Smouha',
                'name_ar' => 'سموحة',
                'shipping_price' => 25.00,
            ],
            [
                'city_id' => $giza->id,
                'name_en' => '6th of October',
                'name_ar' => 'السادس من أكتوبر',
                'shipping_price' => 45.00,
            ],
        ];

        foreach ($districts as $district) {
            District::updateOrCreate(
                ['name_en' => $district['name_en'], 'city_id' => $district['city_id']],
                [
                    'name_ar' => $district['name_ar'],
                    'shipping_price' => $district['shipping_price'],
                ]
            );
        }
    }
}
