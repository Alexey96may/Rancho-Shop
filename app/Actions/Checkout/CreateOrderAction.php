<?php

namespace App\Actions\Checkout;

use App\DTO\CheckoutDTO;
use App\Models\Order;

class CreateOrderAction
{
    public function handle(CheckoutDTO $dto, int $totalPrice): Order
    {
        return Order::create([
            'customer_name' => $dto->customerName,
            'customer_phone' => $dto->customerPhone,
            'delivery_address' => $dto->deliveryAddress,
            'customer_comment' => $dto->customerComment,

            'total_price' => $totalPrice,
            'delivery_price' => 0,
            'discount_total' => 0,

            'status' => 'new',
        ]);
    }
}