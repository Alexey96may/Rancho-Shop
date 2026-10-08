<?php

namespace App\Actions\Checkout;

use Illuminate\Support\Collection;
use App\DTO\CheckoutDTO;

class DecrementStockAction
{
    /**
     * @param Collection<int, \App\Models\ProductVariant> $variants Индексированная коллекция вариаций
     */
    public function handle(CheckoutDTO $dto, Collection $variants): void
    {
        foreach ($dto->items as $item) {
            $variant = $variants->get($item->variantId);

            if ($variant) {
                $variant->decrement('stock', $item->quantity);
            }
        }
    }
}
