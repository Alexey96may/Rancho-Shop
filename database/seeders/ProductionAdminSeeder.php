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
        $phone = config('app.admin_phone');
        $password = config('app.admin_password');

        if (!$phone || !$password) {
            throw new RuntimeException(
                'Set ADMIN_PHONE and ADMIN_PASSWORD in .env before seeding production.'
            );
        }

        if (strlen($password) < 12) {
            throw new RuntimeException('ADMIN_PASSWORD must be at least 12 characters.');
        }

        $normalizedPhone = $this->normalizePhone($phone);

        User::updateOrCreate(
            ['phone' => $normalizedPhone],
            [
                'name' => 'Admin',
                'email' => config('app.admin_email'), // опционально
                'password' => Hash::make($password),
                'password_set_at' => now(),
                'role' => 'admin',
            ],
        );

        $this->command->info("Production admin ensured: {$normalizedPhone}");
    }

    /**
     * Нормализует телефон к маске +7 (999) 123-45-67.
     * Та же логика, что в LoginRequest::normalizePhone.
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 10) {
            $digits = '7' . $digits;
        }
        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }

        if (strlen($digits) !== 11 || $digits[0] !== '7') {
            throw new RuntimeException("Invalid ADMIN_PHONE: {$phone}");
        }

        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($digits, 1, 3),
            substr($digits, 4, 3),
            substr($digits, 7, 2),
            substr($digits, 9, 2),
        );
    }
}
