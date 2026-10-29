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

        if ($user->user_id) {
            $lines[] = '<b>User ID:</b> ' . $user->user_id;
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
            $lines[] = '<b>User ID:</b> ' . $user->user_id;
        }

        $lines[] = '';
        $lines[] = '<b>Последнее сообщение:</b>';
        $lines[] = e(mb_substr($user->content, 0, 500));

        return implode("\n", $lines);
    }
}
