<?php

namespace App\Actions\Checkout;

use Illuminate\Support\Collection;
use App\DTO\CheckoutDTO;

class CalculateOrderPriceAction
{
    public function handle(CheckoutDTO $dto, Collection $products): int
    {
        $total = 0;

        foreach ($dto->items as $item) {

            $product = $products->get($item->productId);

            $variant = $product->variants
                ->firstWhere('id', $item->variantId);

            $total += $variant->price * $item->quantity;
        }

        return $total;
    }
}