<?php

namespace App\Actions\Checkout;

use App\DTO\CheckoutDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResolveCheckoutUserAction
{
    /**
    * @return array{user: ?User, justCreated: bool}
    *
    */
    public function handle(CheckoutDTO $dto): array
    {
        // 1. Авторизован — отдаём его
        if ($dto->userId !== null) {
            return ['user' => User::find($dto->userId), 'justCreated' => false];
        }

        // 2. Галочку не ставил — гостевой заказ
        if (! $dto->createAccount) {
            return ['user' => null, 'justCreated' => false];
        }

        // 3. Телефон уже занят — привязываем к существующему
        $existing = User::where('phone', $dto->customerPhone)->first();
        if ($existing) {
            return ['user' => $existing, 'justCreated' => false];
        }

        // 4. Создаём нового
        $password = Str::password(10);

        $user = User::create([
            'name'     => $dto->customerName,
            'phone'    => $dto->customerPhone,
            'password' => Hash::make($password),
        ]);

        // SMS пока просто в лог — заменить на  SmsService позже
        Log::info('New user from checkout', [
            'user_id' => $user->id,
            'phone'   => $user->phone,
            'password'=> $password,
        ]);

        return ['user' => $user, 'justCreated' => true];
    }
}
