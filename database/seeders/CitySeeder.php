<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run()
    {
        $countries = [
            1 => [ // Egypt
                ['name_en' => 'Cairo', 'name_ar' => 'القاهرة', 'shipping_price' => 250],
                ['name_en' => 'Alexandria', 'name_ar' => 'الإسكندرية', 'shipping_price' => 260],
                ['name_en' => 'Giza', 'name_ar' => 'الجيزة', 'shipping_price' => 245],
                ['name_en' => 'Mansoura', 'name_ar' => 'المنصورة', 'shipping_price' => 255],
            ],
            2 => [ // Saudi Arabia
                ['name_en' => 'Riyadh', 'name_ar' => 'الرياض', 'shipping_price' => 70],
                ['name_en' => 'Jeddah', 'name_ar' => 'جدة', 'shipping_price' => 62],
                ['name_en' => 'Dammam', 'name_ar' => 'الدمام', 'shipping_price' => 58],
                ['name_en' => 'Mecca', 'name_ar' => 'مكة', 'shipping_price' => 45],
            ],
            5 => [ // UAE
                ['name_en' => 'Dubai', 'name_ar' => 'دبي', 'shipping_price' => 30],
                ['name_en' => 'Abu Dhabi', 'name_ar' => 'أبو ظبي', 'shipping_price' => 28],
                ['name_en' => 'Sharjah', 'name_ar' => 'الشارقة', 'shipping_price' => 26],
                ['name_en' => 'Al Ain', 'name_ar' => 'العين', 'shipping_price' => 25],
            ],
            6 => [ // Kuwait
                ['name_en' => 'Kuwait City', 'name_ar' => 'مدينة الكويت', 'shipping_price' => 15],
                ['name_en' => 'Hawalli', 'name_ar' => 'حولي', 'shipping_price' => 13],
                ['name_en' => 'Salmiya', 'name_ar' => 'السالمية', 'shipping_price' => 12],
                ['name_en' => 'Fahaheel', 'name_ar' => 'الفحيحيل', 'shipping_price' => 11],
            ],
            3 => [ // Qatar
                ['name_en' => 'Doha', 'name_ar' => 'الدوحة', 'shipping_price' => 30],
                ['name_en' => 'Al Wakrah', 'name_ar' => 'الوكرة', 'shipping_price' => 32],
                ['name_en' => 'Al Rayyan', 'name_ar' => 'الريان', 'shipping_price' => 43],
                ['name_en' => 'Umm Salal', 'name_ar' => 'أم صلال', 'shipping_price' => 44],
            ],
            7 => [ // Bahrain
                ['name_en' => 'Manama', 'name_ar' => 'المنامة', 'shipping_price' => 55],
                ['name_en' => 'Muharraq', 'name_ar' => 'المحرق', 'shipping_price' => 56],
                ['name_en' => 'Riffa', 'name_ar' => 'الرفاع', 'shipping_price' => 47],
                ['name_en' => 'Isa Town', 'name_ar' => 'مدينة عيسى', 'shipping_price' => 48],
            ],
        ];

        foreach ($countries as $countryId => $cities) {
            foreach ($cities as $city) {
                City::create([
                    'name_en' => $city['name_en'],
                    'name_ar' => $city['name_ar'],
                    'country_id' => $countryId,
                    'shipping_price' => $city['shipping_price'],
                ]);
            }
        }
    }
}
