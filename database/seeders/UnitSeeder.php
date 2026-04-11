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
            ['name' => 'килограмм', 'code' => 'kg'],
            ['name' => 'грамм', 'code' => 'g'],
            ['name' => 'литр', 'code' => 'l'],
            ['name' => 'миллилитр', 'code' => 'ml'],
            ['name' => 'штука', 'code' => 'pcs'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['code' => $unit['code']], $unit);
        }
    }
}
