<?php

namespace Database\Seeders;

use App\Models\DeliveryAddress;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeliveryAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $users = User::all();

        foreach ($users as $user) {
            DeliveryAddress::factory()
                ->count(2)
                ->create([
                    'user_id' => $user->id,
                ]);
        }
    }
}
