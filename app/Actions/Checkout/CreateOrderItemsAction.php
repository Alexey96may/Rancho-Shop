<?php

namespace App\Actions\Checkout;

use Illuminate\Support\Collection;
use App\Models\Order;
use App\DTO\CheckoutDTO;

class CreateOrderItemsAction
{
    public function handle(Order $order, CheckoutDTO $dto, Collection $products): void
    {
        foreach ($dto->items as $item) {
            $product = $products->get($item->productId);

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price_at_purchase' => $product->price,
                'base_price_at_purchase' => $product->price,
                'quantity' => $item->quantity,
            ]);
        }
    }
}