<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Товар снова в наличии</title></head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Здравствуйте, {{ $user->name ?? 'друг' }}!</h2>

    <p>
        Товар <strong>«{{ $product->name }}»</strong>, на который вы подписались,
        снова доступен для заказа.
    </p>

    <p>
        <a href="{{ url('/catalog/' . $product->slug) }}"
           style="display:inline-block;padding:10px 20px;background:#2f6f4e;color:#fff;text-decoration:none;border-radius:8px;">
            Открыть товар
        </a>
    </p>

    <p style="color:#888;font-size:12px;">
        Вы получили это письмо, потому что подписались на уведомления об этом товаре.
    </p>
</body>
</html>
