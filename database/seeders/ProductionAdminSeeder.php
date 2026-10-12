<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ProductionAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (!$email || !$password) {
            throw new RuntimeException(
                'Set ADMIN_EMAIL and ADMIN_PASSWORD in .env before seeding production.'
            );
        }

        if (strlen($password) < 12) {
            throw new RuntimeException('ADMIN_PASSWORD must be at least 12 characters.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
            ],
        );

        $this->command->info("Production admin ensured: {$email}");
    }
}
