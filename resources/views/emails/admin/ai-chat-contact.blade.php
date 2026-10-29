<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Пользователь оставил контакт</title>
</head>
<body style="font-family: -apple-system, sans-serif; line-height: 1.5; color: #1a1712; max-width: 640px; margin: 0 auto; padding: 24px;">
    <h2 style="margin-top: 0;">📬 Пользователь оставил контакт</h2>

    <p><strong>Контакт:</strong> {{ $contact['value'] }}</p>
    <p><strong>Тип:</strong> {{ $contact['type'] === 'phone' ? 'Телефон' : 'Email' }}</p>
    <p><strong>Сессия:</strong> {{ $userMessage->session_id ?? '—' }}</p>

    @if ($userMessage->user_id)
        <p><strong>User ID:</strong> {{ $userMessage->user_id }}</p>
    @endif

    <hr style="border: none; border-top: 1px solid #E8E3D6; margin: 24px 0;">

    <h3>Последнее сообщение:</h3>
    <blockquote style="border-left: 3px solid #7AB89A; padding: 8px 12px; margin: 0; background: #FCFAF5; color: #403A2D;">
        {{ $userMessage->content }}
    </blockquote>
</body>
</html>
