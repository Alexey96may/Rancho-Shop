<?php

namespace App\Actions\Checkout;

use App\DTO\CheckoutDTO;
use App\Models\Product;
use Illuminate\Support\Collection;

class GetProductsAction
{
    public function handle(CheckoutDTO $dto): Collection
    {
        return Product::whereIn(
            'id',
            $dto->items->pluck('productId')
        )
        ->lockForUpdate()
        ->get()
        ->keyBy('id');
    }
}