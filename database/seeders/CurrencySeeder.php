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
            ['name' => 'EGP', 'country_code' => 'EG', 'is_default' => true],
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
