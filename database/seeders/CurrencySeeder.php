<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['name' => 'EGP'],
            ['name' => 'KWD'], // Kuwaiti Dinar
            ['name' => 'USD'],
            ['name' => 'EUR'],
            ['name' => 'GBP'],
            ['name' => 'JPY'],
            ['name' => 'CAD'],
            ['name' => 'AUD'],
            ['name' => 'CHF'],
            ['name' => 'CNY'],
            ['name' => 'INR'],
            ['name' => 'BRL'],
            ['name' => 'MXN'],
            ['name' => 'ARS'],
            ['name' => 'CLP'],
            ['name' => 'COP'],
            ['name' => 'PEN'],
            ['name' => 'PYG'],
            ['name' => 'UYU'],
            ['name' => 'RUB'],
            ['name' => 'SAR'], // Saudi Riyal
            ['name' => 'AED'], // UAE Dirham
            ['name' => 'BHD'], // Bahraini Dinar
            ['name' => 'OMR'], // Omani Rial
            ['name' => 'QAR'], // Qatari Rial
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate($currency);
        }
    }
}
