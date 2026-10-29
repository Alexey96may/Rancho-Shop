<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>AI-чат: требуется внимание</title>
</head>
<body style="font-family: -apple-system, sans-serif; line-height: 1.5; color: #1a1712; max-width: 640px; margin: 0 auto; padding: 24px;">
    <h2 style="margin-top: 0;">🤖 AI-чат: требуется внимание</h2>

    <p><strong>Причина:</strong> {{ $reason }}</p>
    <p><strong>Сессия:</strong> {{ $userMessage->session_id ?? '—' }}</p>

    {{-- Данные пользователя --}}
    @if ($userMessage->user)
        <p><strong>Пользователь:</strong> {{ $userMessage->user->name }}</p>

        @if ($userMessage->user->phone)
            <p><strong>Телефон:</strong> {{ $userMessage->user->phone }}</p>
        @endif

        @if ($userMessage->user->email)
            <p><strong>Email:</strong> {{ $userMessage->user->email }}</p>
        @endif

        <p><strong>User ID:</strong> {{ $userMessage->user->id }}</p>
    @elseif ($userMessage->user_id)
        <p><strong>User ID:</strong> {{ $userMessage->user_id }}</p>
    @else
        <p><strong>Гость</strong> (не авторизован)</p>
    @endif

    <hr style="border: none; border-top: 1px solid #E8E3D6; margin: 24px 0;">

    <h3>Вопрос:</h3>
    <blockquote style="border-left: 3px solid #7AB89A; padding: 8px 12px; margin: 0; background: #FCFAF5; color: #403A2D;">
        {{ $userMessage->content }}
    </blockquote>

    <h3>Ответ бота:</h3>
    <blockquote style="border-left: 3px solid #D97706; padding: 8px 12px; margin: 0; background: #FCFAF5; color: #403A2D;">
        {{ $assistantMessage->content }}
    </blockquote>

    <hr style="border: none; border-top: 1px solid #E8E3D6; margin: 24px 0;">

    <p style="font-size: 12px; color: #948B74;">
        <a href="{{ url('/admin/ai-messages?session=' . $userMessage->session_id) }}" style="color: #3B7558;">
            Открыть диалог в админке →
        </a>
    </p>
</body>
</html>
