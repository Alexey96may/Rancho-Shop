<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ #{{ $order->id }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Здравствуйте, {{ $order->customer_name ?? 'друг' }}!</h2>

    <p>
        Статус вашего заказа <strong>#{{ $order->id }}</strong> обновлён:
        <strong>{{ $newStatusLabel }}</strong>.
    </p>

    <p>
        <strong>Сумма заказа:</strong>
        {{ number_format((float) $order->total_price / 100, 2, '.', ' ') }} ₽
    </p>

    @if ($order->delivery_address)
        <p><strong>Адрес доставки:</strong> {{ $order->delivery_address }}</p>
    @else
        <p><strong>Самовывоз</strong></p>
    @endif

    <p>Спасибо, что выбрали нас!</p>

    <p style="color: #888; font-size: 12px;">
        Это автоматическое письмо, отвечать на него не нужно.
    </p>
</body>
</html>
