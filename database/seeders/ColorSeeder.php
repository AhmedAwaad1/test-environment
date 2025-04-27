<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('colors')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $colors = [
            [
                'name_en' => 'Red',
                'name_ar' => 'أحمر',
                'hex_code' => '#FF0000',
            ],
            [
                'name_en' => 'Blue',
                'name_ar' => 'أزرق',
                'hex_code' => '#0000FF',
            ],
            [
                'name_en' => 'Green',
                'name_ar' => 'أخضر',
                'hex_code' => '#00FF00',
            ],
        ];

        foreach ($colors as $color) {
            Color::create($color);
        }
    }
}
