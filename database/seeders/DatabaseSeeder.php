<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
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
