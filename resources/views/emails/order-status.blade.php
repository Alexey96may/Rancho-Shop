<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Статус заказа #{{ $order->id }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Заказ #{{ $order->id }} — статус изменён</h2>

    <p>
        Статус: <strong>{{ $oldStatus }}</strong> → <strong>{{ $newStatus }}</strong>
    </p>

    <p>
        <strong>Клиент:</strong> {{ $order->customer_name ?? '—' }}<br>
        <strong>Телефон:</strong> {{ $order->customer_phone ?? '—' }}<br>
        @if ($order->delivery_address)
            <strong>Адрес:</strong> {{ $order->delivery_address }}<br>
        @else
            <strong>Самовывоз</strong><br>
        @endif
        <strong>Итого:</strong> {{ number_format((float) $order->total_price / 100, 2, '.', ' ') }} ₽
    </p>

    @if ($order->customer_comment)
        <p><strong>Комментарий:</strong> {{ $order->customer_comment }}</p>
    @endif

    <p style="color: #888; font-size: 12px;">
        Письмо отправлено автоматически. Не отвечайте на него.
    </p>
</body>
</html>
