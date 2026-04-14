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
            $variant = $products
                ->pluck('variants')
                ->flatten(1)
                ->firstWhere('id', $item->variantId);

            if (!$variant) {
                throw new ProductNotAvailableException($item->variantId);
            }

            $product = $variant->product;

            if (!$product || !$product->is_active) {
                throw new ProductNotAvailableException($item->variantId);
            }

            if ($variant->isInStock($item->quantity)) {

                Log::warning('Insufficient stock', [
                    'variant_id' => $variant->id,
                    'requested' => $item->quantity,
                    'available' => $variant->stock,
                ]);

                throw new InsufficientStockException($item->variantId);
            }
        }
    }
}