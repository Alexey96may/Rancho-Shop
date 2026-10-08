<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Получить данные или URL для оплаты заказа
     */
    public function getPaymentData(Order $order): array;

    /**
     * Проверить подпись Webhook/Callback
     */
    public function validateCallback(array $data): bool;
}
