<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\ProductVariant;

use App\DTO\CartItemDTO;

class CartService
{
    public function validate(Collection $items): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        $variants = ProductVariant::whereIn(
            'id',
            $items->pluck('variantId')
            )
            ->with('product')
            ->get()
            ->keyBy(fn ($v) => (int) $v->id);

        return $items->map(function (CartItemDTO $item) use ($variants) {
            $variant = $variants->get($item->variantId);

            if (!$variant || !$variant->is_active) {
                return [
                    'variant_id' => $item->variantId,
                    'valid' => false,
                    'reason' => 'not_available',
                ];
            }

            if ($variant->stock <= 0) {
                return [
                    'variant_id' => $item->variantId,
                    'valid' => false,
                    'reason' => 'out_of_stock',
                ];
            }

            return [
                'variant_id' => $variant->id,
                'valid' => true,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'quantity' => min($item->quantity, $variant->stock),
            ];
        })->values()->toArray();
    }
}