<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('currencies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $currencies = [
            ['name' => 'EGP', 'country_code' => 'EG'],
            ['name' => 'KWD', 'country_code' => 'KW', 'is_default' => true],
            ['name' => 'USD', 'country_code' => 'US'],
            ['name' => 'EUR', 'country_code' => 'EU'],
            ['name' => 'GBP', 'country_code' => 'GB'],
            ['name' => 'JPY', 'country_code' => 'JP'],
            ['name' => 'CAD', 'country_code' => 'CA'],
            ['name' => 'AUD', 'country_code' => 'AU'],
            ['name' => 'CHF', 'country_code' => 'CH'],
            ['name' => 'CNY', 'country_code' => 'CN'],
            ['name' => 'INR', 'country_code' => 'IN'],
            ['name' => 'BRL', 'country_code' => 'BR'],
            ['name' => 'MXN', 'country_code' => 'MX'],
            ['name' => 'ARS', 'country_code' => 'AR'],
            ['name' => 'CLP', 'country_code' => 'CL'],
            ['name' => 'COP', 'country_code' => 'CO'],
            ['name' => 'PEN', 'country_code' => 'PE'],
            ['name' => 'PYG', 'country_code' => 'PY'],
            ['name' => 'UYU', 'country_code' => 'UY'],
            ['name' => 'RUB', 'country_code' => 'RU'],
            ['name' => 'SAR', 'country_code' => 'SA'],
            ['name' => 'AED', 'country_code' => 'AE'],
            ['name' => 'BHD', 'country_code' => 'BH'],
            ['name' => 'OMR', 'country_code' => 'OM'],
            ['name' => 'QAR', 'country_code' => 'QA'],
        ];

        foreach ($currencies as $currency) {
            Currency::create([
                'name' => $currency['name'],
                'country_code' => $currency['country_code'],
                'is_default' => $currency['is_default'] ?? false,
            ]);
        }
    }
}
