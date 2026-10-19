<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('UserSeeder skipped in production.');

            return;
        }

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@rancho.com',
            'phone' => '+7 (999) 111-11-11', // <-- маска
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@rancho.com',
            'phone' => '+7 (999) 222-22-22',
            'password' => Hash::make('customer'),
            'role' => 'customer',
        ]);

        User::factory()->create([
            'name' => 'Moderator',
            'email' => 'moderator@rancho.com',
            'phone' => '+7 (999) 333-33-33',
            'password' => Hash::make('moderator'),
            'role' => 'moderator',
        ]);

        User::factory()->create([
            'name' => 'Worker',
            'email' => 'worker@rancho.com',
            'phone' => '+7 (999) 444-44-44',
            'password' => Hash::make('worker'),
            'role' => 'worker',
        ]);

        User::factory(30)->create();
    }
}
