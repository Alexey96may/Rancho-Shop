<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@rancho.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@rancho.com',
            'password' => Hash::make('customer'),
            'role' => 'customer',
        ]);

        User::factory()->create([
            'name' => 'Moderator',
            'email' => 'moderator@rancho.com',
            'password' => Hash::make('moderator'),
            'role' => 'moderator',
        ]);

        User::factory()->create([
            'name' => 'Worker',
            'email' => 'worker@rancho.com',
            'password' => Hash::make('worker'),
            'role' => 'worker',
        ]);

        User::factory(10)->create();

        $this->call([
            DeliveryAddressSeeder::class,
            CategorySeeder::class,
            AnimalSeeder::class,
            UnitSeeder::class,
            SettingSeeder::class,
            PageSeeder::class,
            FaqSeeder::class,
            LandingBlockSeeder::class,
            SeoSeeder::class,

            ProductSeeder::class,

            PromoCodeSeeder::class,
            OrderSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
