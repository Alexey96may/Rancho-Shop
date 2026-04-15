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
            ['name' => 'грамм', 'slug' => 'g', 'short' => 'гр'],
            ['name' => 'литр', 'slug' => 'l', 'short' => 'л'],
            ['name' => 'миллилитр', 'slug' => 'ml', 'short' => 'мл'],
            ['name' => 'штука', 'slug' => 'pcs', 'short' => 'шт'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['slug' => $unit['slug']], $unit);
        }
    }
}
