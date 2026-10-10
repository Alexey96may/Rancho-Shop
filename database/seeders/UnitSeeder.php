<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $units = [
            ['name' => 'килограмм',  'slug' => 'kg',    'short' => 'кг',  'low_stock_threshold' => 0.5],
            ['name' => 'грамм',      'slug' => 'g',     'short' => 'г',   'low_stock_threshold' => 50],

            ['name' => 'литр',       'slug' => 'l',     'short' => 'л',   'low_stock_threshold' => 0.5],
            ['name' => 'миллилитр',  'slug' => 'ml',    'short' => 'мл',  'low_stock_threshold' => 50],

            ['name' => 'штука',      'slug' => 'pcs',   'short' => 'шт',  'low_stock_threshold' => 3],
            ['name' => 'упаковка',   'slug' => 'pack',  'short' => 'уп',  'low_stock_threshold' => 2],
            ['name' => 'десяток',    'slug' => 'ten',   'short' => 'дес', 'low_stock_threshold' => 2],
            ['name' => 'лоток',      'slug' => 'tray',  'short' => 'лт',  'low_stock_threshold' => 1],

            ['name' => 'пучок',      'slug' => 'bunch', 'short' => 'пуч', 'low_stock_threshold' => 2],
            ['name' => 'банка',      'slug' => 'jar',   'short' => 'бан', 'low_stock_threshold' => 2],
            ['name' => 'головка',    'slug' => 'head',  'short' => 'гол', 'low_stock_threshold' => 1],
            ['name' => 'сетка',      'slug' => 'mesh',  'short' => 'сет', 'low_stock_threshold' => 1],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['slug' => $unit['slug']],
                $unit,
            );
        }
    }
}
