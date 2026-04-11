<?php

namespace App\Actions\Checkout;

use Illuminate\Support\Collection;
use App\DTO\CheckoutDTO;
use App\Exceptions\Checkout\ProductNotAvailableException;
use App\Exceptions\Checkout\InsufficientStockException;
use Illuminate\Support\Facades\Log;

class ValidateCartAction
{
    public function handle(CheckoutDTO $dto, Collection $products): void
    {
        foreach ($dto->items as $item) {
            $product = $products->get($item->productId);

            if (!$product || !$product->is_active) {
                throw new ProductNotAvailableException($item->productId);
            }

            if ($product->stock < $item->quantity) {
                
                Log::warning('Insufficient stock', [
                    'product_id' => $product->id,
                    'requested' => $item->quantity,
                    'available' => $product->stock,
                ]);

                throw new InsufficientStockException($item->productId);
            }
        }

        Log::info('Cart validated', [
            'items' => $dto->items->count(),
        ]);
    }
}