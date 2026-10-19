<?php

namespace App\Services\Telegram;

use App\Models\User;

class UserTelegramNotifier
{
    public function __construct(
        private readonly TelegramService $telegram,
    ) {}

    public function notifyNewUser(User $user): void
    {
        $this->telegram->sendToAdmin($this->formatMessage($user));
    }

    private function formatMessage(User $user): string
    {
        $lines = [];
        $lines[] = '👤 <b>Новый пользователь</b>';
        $lines[] = '';
        $lines[] = '<b>Имя:</b> ' . e($user->name ?? '—');
        $lines[] = '<b>Телефон:</b> ' . e($user->phone);

        if ($user->role) {
            $label = method_exists($user->role, 'label')
                ? $user->role->label()
                : (string) $user->role;

            $lines[] = '<b>Роль:</b> ' . e($label);
        }

        $lines[] = '<b>ID:</b> ' . $user->id;
        $lines[] = '<b>Зарегистрирован:</b> '
            . $user->created_at?->timezone(config('app.timezone_display', 'Europe/Moscow'))
                ->format('d.m.Y H:i');

        return implode("\n", $lines);
    }
}
