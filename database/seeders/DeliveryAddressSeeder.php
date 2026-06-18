<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DeliveryAddress;

class DeliveryAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
