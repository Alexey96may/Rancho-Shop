<?php

namespace App\Services\Telegram;

use App\Models\Order;

class OrderTelegramNotifier
{
    public function __construct(
        private readonly TelegramService $telegram,
    ) {}

    public function notifyNewOrder(Order $order): void
    {
        $this->telegram->sendToAdmin($this->formatMessage($order));
    }

    private function formatMessage(Order $order): string
    {
        $order->loadMissing(['items', 'user', 'promoCode']);

        $lines = [];
        $lines[] = '🛒 <b>Новый заказ #' . $order->id . '</b>';
        $lines[] = '';
        $lines[] = '<b>Клиент:</b> ' . e($order->customer_name ?? '—');
        $lines[] = '<b>Телефон:</b> ' . e($order->customer_phone ?? '—');

        if ($order->delivery_address) {
            $lines[] = '<b>Адрес:</b> ' . e($order->delivery_address);
        } else {
            $lines[] = '<b>Самовывоз</b>';
        }

        $lines[] = '';
        $lines[] = '<b>Состав:</b>';

        foreach ($order->items as $item) {
            $name = $item->product_name
                ?? $item->variant?->product?->name
                ?? 'Товар';

            $price = (float) $item->unit_price * (int) $item->quantity;

            $lines[] = sprintf(
                '• %s × %d — %s',
                e($name),
                $item->quantity,
                $this->money($price)
            );
        }

        $lines[] = '';

        // ────── Подытог (без доставки и скидки) ──────
        $subtotal = $order->items->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity);
        $lines[] = '<b>Подытог:</b> ' . $this->money($subtotal);

        // ────── Промокод ──────
        if ($order->promoCode) {
            $promoLabel = $order->promoCode->code;
            if ($order->promoCode->type?->value === 'percent') {
                $promoLabel .= ' (' . $order->promoCode->value . '%)';
            }
            $lines[] = '<b>Промокод:</b> ' . e($promoLabel);
        }

        // ────── Скидка ──────
        if ((float) $order->discount_total > 0) {
            $lines[] = '<b>Скидка:</b> −' . $this->money($order->discount_total);
        }

        // ────── Доставка ──────
        if ($order->is_pickup) {
            $lines[] = '<b>Доставка:</b> самовывоз';
        } else {
            $lines[] = '<b>Доставка:</b> '
                . ((float) $order->delivery_price > 0
                    ? $this->money($order->delivery_price)
                    : 'бесплатно');
        }

        $lines[] = '';
        $lines[] = '<b>Итого:</b> ' . $this->money($order->total_price);

        if ($order->customer_comment) {
            $lines[] = '';
            $lines[] = '<b>Комментарий:</b> ' . e($order->customer_comment);
        }

        return implode("\n", $lines);
    }

    private function money(float|int|string|null $value): string
    {
        return number_format((float) $value / 100, 2, '.', ' ') . ' ₽';
    }
}
