<?php

namespace App\Actions\Checkout;

use App\DTO\CheckoutDTO;
use App\DTO\DeliveryDTO;
use App\Models\Order;
use App\Models\User;

class CreateOrderAction
{
    public function handle(CheckoutDTO $dto, int $totalPrice, DeliveryDTO $delivery, array $deliveryResult, ?User $user = null): Order
    {
        $deliveryPrice = $deliveryResult['delivery_price'];

        if (
            $deliveryResult['zone'] &&
            $totalPrice >= ($deliveryResult['zone']['free_from'] ?? 0)
        ) {
            $deliveryPrice = 0;
        }

        return Order::create([
            'user_id' => $user?->id,
            'customer_name' => $dto->customerName,
            'customer_phone' => $dto->customerPhone,

            'delivery_address' => $delivery->is_pickup ? null : $delivery->address,
            'delivery_lat' => $delivery->lat,
            'delivery_lng' => $delivery->lng,

            'is_pickup' => $delivery->is_pickup,
            'delivery_validated' => $deliveryResult['is_valid'],
            'delivery_meta' => $deliveryResult,

            'customer_comment' => $dto->customerComment,

            'total_price' => $totalPrice + $deliveryPrice,
            'delivery_price' => $deliveryPrice,
            'discount_total' => 0,        // 'discount_total' => $total['discount'],

            'status' => 'new',
            'promo_code_id' => null,  //$dto->promo_code_id,
        ]);
    }
}
