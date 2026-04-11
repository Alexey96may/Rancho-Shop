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
            'name' => 'Admin Rancho',
            'email' => 'admin@rancho.com',
            'password' => Hash::make('admin'),
        ]);

        User::factory(10)->create();

        $this->call([
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
