<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Unit;

class UnitSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'килограмм', 'slug' => 'kg', 'short' => 'кг'],
            ['name' => 'грамм', 'slug' => 'g', 'short' => 'г'],
            
            ['name' => 'литр', 'slug' => 'l', 'short' => 'л'],
            ['name' => 'миллилитр', 'slug' => 'ml', 'short' => 'мл'],
            
            ['name' => 'штука', 'slug' => 'pcs', 'short' => 'шт'],
            ['name' => 'упаковка', 'slug' => 'pack', 'short' => 'уп'],
            ['name' => 'десяток', 'slug' => 'ten', 'short' => 'дес'], 
            ['name' => 'лоток', 'slug' => 'tray', 'short' => 'лт'],
            
            ['name' => 'пучок', 'slug' => 'bunch', 'short' => 'пуч'],
            ['name' => 'банка', 'slug' => 'jar', 'short' => 'бан'],
            ['name' => 'головка', 'slug' => 'head', 'short' => 'гол'],
            ['name' => 'сетка', 'slug' => 'mesh', 'short' => 'сет'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['slug' => $unit['slug']], $unit);
        }
    }
}
