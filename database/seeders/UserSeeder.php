<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@rancho.com',
            'phone' => '+79991111111',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@rancho.com',
            'phone' => '+79992222222',
            'password' => Hash::make('customer'),
            'role' => 'customer',
        ]);

        User::factory()->create([
            'name' => 'Moderator',
            'email' => 'moderator@rancho.com',
            'phone' => '+79993333333',
            'password' => Hash::make('moderator'),
            'role' => 'moderator',
        ]);

        User::factory()->create([
            'name' => 'Worker',
            'email' => 'worker@rancho.com',
            'phone' => '+79994444444',
            'password' => Hash::make('worker'),
            'role' => 'worker',
        ]);

        User::factory(30)->create();
    }
}
