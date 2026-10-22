<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ #{{ $order->id }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>🛒 Заказ #{{ $order->id }} — статус изменён</h2>

    <p>
        Статус: <strong>{{ $oldStatus }}</strong> → <strong>{{ $newStatus }}</strong>
    </p>

    <h3>Клиент</h3>
    <p>
        <strong>Имя:</strong> {{ $order->customer_name ?? '—' }}<br>
        <strong>Телефон:</strong> {{ $order->customer_phone ?? '—' }}<br>
        @if ($order->user?->email)
            <strong>Email:</strong> {{ $order->user->email }}<br>
        @endif
        @if ($order->delivery_address)
            <strong>Адрес:</strong> {{ $order->delivery_address }}<br>
        @else
            <strong>Самовывоз</strong><br>
        @endif
    </p>

    <h3>Состав</h3>
    <ul>
        @foreach ($order->items as $item)
            <li>
                {{ $item->product_name ?? $item->variant?->product?->name ?? 'Товар' }}
                × {{ rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.') }}
                — {{ number_format((float) $item->unit_price * (float) $item->quantity / 100, 2, '.', ' ') }} ₽
            </li>
        @endforeach
    </ul>

    <p>
        <strong>Итого:</strong>
        {{ number_format((float) $order->total_price / 100, 2, '.', ' ') }} ₽
    </p>

    @if ($order->customer_comment)
        <p><strong>Комментарий:</strong> {{ $order->customer_comment }}</p>
    @endif

    <p style="color: #888; font-size: 12px;">Письмо отправлено автоматически администратору.</p>
</body>
</html>
