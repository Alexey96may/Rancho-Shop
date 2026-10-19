<?php

namespace App\Actions\Checkout;

use App\DTO\CheckoutDTO;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ResolveCheckoutUserAction
{
    /**
     * @return array{user: ?User, justCreated: bool}
     */
    public function handle(CheckoutDTO $dto): array
    {
        // 1. Авторизован — отдаём его
        if ($dto->userId !== null) {
            return ['user' => User::find($dto->userId), 'justCreated' => false, 'magicLink' => null];
        }

        // 2. Галочку не ставил — гостевой заказ
        if (!$dto->createAccount) {
            return ['user' => null, 'justCreated' => false, 'magicLink' => null];
        }

        // 3. Телефон уже занят — привязываем к существующему
        $existing = User::where('phone', $dto->customerPhone)->first();
        if ($existing) {
            return ['user' => $existing, 'justCreated' => false, 'magicLink' => null];
        }

        // 4. Создаём нового
        // Пароль генерируем, но никуда не показываем — вход только по magic link.
        // Пользователь сменит пароль в профиле, если захочет.
        $password = Str::password(10);

        $user = User::create([
            'name' => $dto->customerName,
            'phone' => $dto->customerPhone,
            'password' => Hash::make($password),
        ]);

        event(new Registered($user));

        // Генерируем magic link сразу — он будет передан на страницу оплаты.
        // Срок 24 часа: пользователь уходит на шлюз и может вернуться позже.
        $magicLink = URL::temporarySignedRoute(
            'magic.login',
            now()->addHours(24),
            ['user' => $user->id],
        );

        return ['user' => $user, 'justCreated' => true, 'magicLink' => $magicLink];
    }
}
