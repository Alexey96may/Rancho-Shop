<?php

namespace App\Actions\Checkout;

use Illuminate\Support\Collection;
use App\DTO\CheckoutDTO;
use Exception;

class ValidateCartAction
{
    public function handle(CheckoutDTO $dto, Collection $products): void
    {
        foreach ($dto->items as $item) {
            $product = $products->get($item->productId);

            if (!$product || !$product->is_active) {
                throw new Exception("Product {$item->productId} not available");
            }

            if ($product->stock < $item->quantity) {
                throw new Exception("Not enough stock for product {$item->productId}");
            }
        }
    }
}