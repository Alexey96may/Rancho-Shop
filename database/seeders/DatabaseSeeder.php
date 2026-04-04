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
            AnimalSeeder::class,
            ProductSeeder::class,
            PromoCodeSeeder::class,
            SettingSeeder::class,
            PageSeeder::class,
            OrderSeeder::class,
            CommentSeeder::class,
            SeoSeeder::class,
        ]);
    }
}
