<?php

namespace App\Services\Telegram;

use App\Models\AiMessage;

class AiChatTelegramNotifier
{
    public function __construct(
        private readonly TelegramService $telegram,
    ) {}

    public function notifyEscalation(AiMessage $user, AiMessage $assistant, string $reason): void
    {
        $this->telegram->sendToAdmin($this->formatMessage($user, $assistant, $reason));
    }

    private function formatMessage(AiMessage $user, AiMessage $assistant, string $reason): string
    {
        $lines = [];
        $lines[] = '🤖 <b>AI-чат: требуется внимание</b>';
        $lines[] = '';
        $lines[] = '<b>Причина:</b> ' . e($reason);
        $lines[] = '<b>Сессия:</b> <code>' . e($user->session_id ?? '—') . '</code>';

        // Данные авторизованного пользователя
        if ($user->user_id && $user->relationLoaded('user') === false) {
            $user->loadMissing('user');
        }

        if ($user->user) {
            $lines[] = '<b>Пользователь:</b> ' . e($user->user->name ?? '—');

            if ($user->user->phone) {
                $lines[] = '<b>Телефон:</b> ' . e($user->user->phone);
            }

            if ($user->user->email) {
                $lines[] = '<b>Email:</b> ' . e($user->user->email);
            }

            $lines[] = '<b>User ID:</b> ' . $user->user->id;
        } elseif ($user->user_id) {
            $lines[] = '<b>User ID:</b> ' . $user->user_id;
        } else {
            $lines[] = '<b>Гость</b> (не авторизован)';
        }

        $lines[] = '';
        $lines[] = '<b>Вопрос:</b>';
        $lines[] = e(mb_substr($user->content, 0, 500));
        $lines[] = '';
        $lines[] = '<b>Ответ бота:</b>';
        $lines[] = e(mb_substr($assistant->content, 0, 500));

        return implode("\n", $lines);
    }

    public function notifyContact(AiMessage $user, array $contact): void
    {
        $this->telegram->sendToAdmin($this->formatContactMessage($user, $contact));
    }

    private function formatContactMessage(AiMessage $user, array $contact): string
    {
        $label = $contact['type'] === 'phone' ? '📞 Телефон' : '✉️ Email';

        $lines = [];
        $lines[] = '📬 <b>AI-чат: пользователь оставил контакт</b>';
        $lines[] = '';
        $lines[] = '<b>Контакт:</b> ' . e($contact['value']);
        $lines[] = '<b>Тип:</b> ' . e($label);
        $lines[] = '<b>Сессия:</b> <code>' . e($user->session_id ?? '—') . '</code>';

        if ($user->user_id) {
            if (!$user->relationLoaded('user')) {
                $user->loadMissing('user');
            }

            if ($user->user) {
                $lines[] = '<b>Пользователь:</b> ' . e($user->user->name ?? '—');

                if ($user->user->phone) {
                    $lines[] = '<b>Телефон в профиле:</b> ' . e($user->user->phone);
                }

                if ($user->user->email) {
                    $lines[] = '<b>Email в профиле:</b> ' . e($user->user->email);
                }

                $lines[] = '<b>User ID:</b> ' . $user->user->id;
            }
        } else {
            $lines[] = '<b>Гость</b> (не авторизован)';
        }

        $lines[] = '';
        $lines[] = '<b>Последнее сообщение:</b>';
        $lines[] = e(mb_substr($user->content, 0, 500));

        return implode("\n", $lines);
    }
}
