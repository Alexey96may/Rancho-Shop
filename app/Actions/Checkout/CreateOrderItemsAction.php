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

            $variant = $product->variants
                ->firstWhere('id', $item->variantId);

            $order->items()->create([
                'product_variant_id' => $variant->id,

                'product_name' => $product->name,

                'unit_price' => $variant->price,
                'old_unit_price' => $variant->old_price,

                'quantity' => $item->quantity,

                'unit_name' => $variant->unit?->name,
                'unit_code' => $variant->unit?->code,
            ]);
        }
    }
}