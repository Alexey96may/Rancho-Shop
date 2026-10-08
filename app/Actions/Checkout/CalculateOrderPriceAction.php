<?php

namespace App\Actions\Checkout;

use Illuminate\Support\Collection;
use App\DTO\CheckoutDTO;

class CalculateOrderPriceAction
{
    public function handle(CheckoutDTO $dto, Collection $variants): int
    {
        return $dto->items->sum(function ($item) use ($variants) {
            $variant = $variants->get($item->variantId);
            return $variant ? $variant->price * $item->quantity : 0;
        });
    }
}
