<?php

namespace App\Actions\Checkout;

use App\DTO\CheckoutDTO;
use App\Models\Product;
use Illuminate\Support\Collection;

class GetProductsAction
{
    public function handle(CheckoutDTO $dto): Collection
    {
        return Product::with(['variants.unit'])
            ->whereHas('variants', function ($q) use ($dto) {
                $q->whereIn('id', $dto->items->pluck('variantId'));
            })
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }
}