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
            ['name_en' => 'Egypt', 'name_ar' => 'مصر', 'code' => 'EG'],
            ['name_en' => 'Saudi Arabia', 'name_ar' => 'المملكة العربية السعودية', 'code' => 'SA'],
            ['name_en' => 'Qatar', 'name_ar' => 'قطر', 'code' => 'QA'],
            ['name_en' => 'United Arab Emirates', 'name_ar' => 'الإمارات العربية المتحدة', 'code' => 'AE'],
            ['name_en' => 'Kuwait', 'name_ar' => 'الكويت', 'code' => 'KW'],
            ['name_en' => 'Bahrain', 'name_ar' => 'البحرين', 'code' => 'BH'],
            ['name_en' => 'Oman', 'name_ar' => 'عمان', 'code' => 'OM'],
            ['name_en' => 'Jordan', 'name_ar' => 'الأردن', 'code' => 'JO'],
            ['name_en' => 'Lebanon', 'name_ar' => 'لبنان', 'code' => 'LB'],
            ['name_en' => 'Iraq', 'name_ar' => 'العراق', 'code' => 'IQ'],
            ['name_en' => 'Palestine', 'name_ar' => 'فلسطين', 'code' => 'PS'],
            ['name_en' => 'Yemen', 'name_ar' => 'اليمن', 'code' => 'YE'],
            ['name_en' => 'Syria', 'name_ar' => 'سوريا', 'code' => 'SY'],
            ['name_en' => 'Libya', 'name_ar' => 'ليبيا', 'code' => 'LY'],
            ['name_en' => 'Morocco', 'name_ar' => 'المغرب', 'code' => 'MA'],
            ['name_en' => 'Tunisia', 'name_ar' => 'تونس', 'code' => 'TN'],
            ['name_en' => 'Algeria', 'name_ar' => 'الجزائر', 'code' => 'DZ'],
            ['name_en' => 'Sudan', 'name_ar' => 'السودان', 'code' => 'SD'],
        ];

        foreach ($countries as $data) {
            Country::updateOrCreate(
                ['name_ar' => $data['name_ar']],
                [
                    'name_en' => $data['name_en'],
                    'country_code' => $data['code'],
                ]
            );
        }
    }
}
