<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountryCodeSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name_en' => 'Saudi Arabia', 'name_ar' => 'المملكة العربية السعودية', 'code' => 'SA'],
            ['name_en' => 'Qatar', 'name_ar' => 'قطر', 'code' => 'QA'],
            ['name_en' => 'United Arab Emirates', 'name_ar' => 'الإمارات العربية المتحدة', 'code' => 'AE'],
            ['name_en' => 'Kuwait', 'name_ar' => 'الكويت', 'code' => 'KW'],
            ['name_en' => 'Bahrain', 'name_ar' => 'البحرين', 'code' => 'BH'],
            ['name_en' => 'Oman', 'name_ar' => 'عمان', 'code' => 'OM'],
        ];


        $currencyRates = [
            'SA' => 12.3,
            'QA' => 11.9,
            'AE' => 11.2,
            'BH' => 1.26,
            'OM' => 1.25,
        ];

        foreach ($countries as $data) {
            if ($data['code'] === 'KW') {
                $shippingPrice = 3;
            } else {
                $rate = $currencyRates[$data['code']] ?? null;

                if (!$rate) {
                    continue;
                }

                $shippingPrice = 10 * $rate;
            }

            Country::updateOrCreate(
                ['name_ar' => $data['name_ar']],
                [
                    'name_en' => $data['name_en'],
                    'country_code' => $data['code'],
                    'shipping_price' => $shippingPrice,
                ]
            );
        }
    }
}
