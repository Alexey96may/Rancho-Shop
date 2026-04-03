<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PromoCode;

class PromoCodeSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = [
            [
                'code' => 'ZORKA2026',
                'type' => 'percent',
                'value' => 1000,
                'min_order_amount' => 100000,
                'max_discount' => 50000,
                'usage_limit' => 100,
                'is_active' => true,
            ],
            [
                'code' => 'WELCOME',
                'type' => 'fixed',
                'value' => 20000,
                'min_order_amount' => 50000,
                'is_active' => true,
            ],
            [
                'code' => 'EXPIRED_CODE',
                'type' => 'fixed',
                'value' => 50000,
                'expires_at' => now()->subDays(1), // Already expired
                'is_active' => true,
            ]
        ];

        foreach ($codes as $code) {
            PromoCode::create($code);
        }
    }
}
