<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\Product;

use App\DTO\CartItemDTO;

class CartService
{
    public function validate(Collection $items): array
    {
        $products = Product::whereIn(
            'id',
            $items->pluck('product_id')
        )->get()->keyBy('id');

        return $items->map(function (CartItemDTO $item) use ($products) {
            $product = $products[$item->productId] ?? null;

            if (!$product || !$product->is_available) {
                return [
                    'product_id' => $item->productId,
                    'valid' => false,
                    'reason' => 'not_available',
                ];
            }

            return [
                'product_id' => $product->id,
                'valid' => true,
                'price' => $product->price_rub,
                'stock' => $product->stock,
            ];
        })->values()->toArray();
    }
}