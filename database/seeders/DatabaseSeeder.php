<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Always — Core Infrastructure
        |--------------------------------------------------------------------------
        | Required for both local and production environments. Reference data, settings, pages.
        */
        $this->call([
            SettingSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            PageSeeder::class,
            FaqSeeder::class,
            LandingBlockSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Local / testing
        |--------------------------------------------------------------------------
        | Products, animals, orders, comments, promo codes, addresses.
        | Do not make it into production.
        */
        if (app()->environment('local', 'testing')) {
            $this->call([
                UserSeeder::class,
                DeliveryAddressSeeder::class,
                AnimalSeeder::class,
                SeoSeeder::class,
                ProductSeeder::class,
                PromoCodeSeeder::class,
                OrderSeeder::class,
                CommentSeeder::class,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Production — first admin only
        |--------------------------------------------------------------------------
        | Created from ADMIN_EMAIL / ADMIN_PASSWORD in .env.
        */
        if (app()->environment('production')) {
            $this->call(ProductionAdminSeeder::class);
        }
    }
}
