<?php

namespace App\Actions\Payments;

use App\Models\Order;

class ValidatePaymentAmountAction
{
    public function handleTinkoff(Order $order, int $amount): bool
    {
        return $amount === (int) $order->total_price;
    }

    public function handleYooKassa(Order $order, string $amount): bool
    {
        $expected = number_format($order->total_price / 100, 2, '.', '');

        return $amount === $expected;
    }
}
