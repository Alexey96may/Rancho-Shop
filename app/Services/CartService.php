<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\Product;

use App\DTO\CartItemDTO;

class CartService
{
    public function validate(Collection $items): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        $products = Product::whereIn(
            'id',
            $items->pluck('productId')
        )->get()->keyBy(fn ($p) => (int) $p->id);

        return $items->map(function (CartItemDTO $item) use ($products) {
            $product = $products->get($item->productId) ?? null;

            if (!$product->isPurchasable($item->quantity)) {
                return [
                    'product_id' => $item->productId,
                    'valid' => false,
                    'reason' => 'Нет в наличии',
                ];
            }

            return [
                'product_id' => $product->id,
                'valid' => true,
                'price' => $product->price_rub,
                'stock' => $product->stock,
                'quantity' => min($item->quantity, $product->stock),
            ];
        })->values()->toArray();
    }
}