<?php

namespace App\Actions\Checkout;

use Illuminate\Support\Collection;
use App\DTO\CheckoutDTO;

class DecrementStockAction
{
    public function handle(CheckoutDTO $dto, Collection $products): void
    {
        foreach ($dto->items as $item) {
            $product = $products->get($item->productId);

            $variant = $product->variants
                ->firstWhere('id', $item->variantId);

            $variant->decrement('stock', $item->quantity);
        }
    }
}