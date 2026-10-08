<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;

class DirectPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Возвращает реквизиты для прямого перевода (СБП / Карта)
     */
    public function getPaymentData(Order $order): array
    {
        return [
            'type' => 'direct',
            'details' => [
                'phone'     => config('payments.direct.phone'),
                'bank'      => config('payments.direct.bank'),
                'recipient' => config('payments.direct.recipient'),
                'note'      => str_replace(
                    ':order_id',
                    (string) $order->id,
                    config('payments.direct.note', 'Оплата заказа #:order_id')
                ),
            ],
        ];
    }

    /**
     * Для ручных платежей автоматическая проверка подписи не требуется
     */
    public function validateCallback(array $data): bool
    {
        return true;
    }
}
