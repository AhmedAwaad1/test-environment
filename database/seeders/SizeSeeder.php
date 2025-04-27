<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sizes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $sizes = [
            'XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'
        ];

        foreach ($sizes as $size) {
            Size::create([
                'name' => $size,
            ]);
        }
    }
}
