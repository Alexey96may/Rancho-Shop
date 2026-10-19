<?php

namespace App\Services;

use App\DTO\CartItemDTO;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    /** Допуск для сравнения float (fmod 0.5 / 0.1 может дать 0.0999...) */
    private const EPSILON = 1e-9;

    public function validate(Collection $items): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        $variants = ProductVariant::whereIn(
            'id',
            $items->pluck('variantId')
        )
            ->with('product.defaultVariant')
            ->get()
            ->keyBy(fn ($v) => (int) $v->id);

        return $items->map(function (CartItemDTO $item) use ($variants) {
            $variant = $variants->get($item->variantId);

            // ── 1. Не найден или продукт выключен
            if (!$variant || ($variant->product && !$variant->product->is_active)) {
                return [
                    'variant_id' => $item->variantId,
                    'valid' => false,
                    'reason' => 'not_available',
                ];
            }

            // ── 2. Подмена на default (variant_id в ответе — ИСХОДНЫЙ)
            if (!$variant->is_default && $variant->product->defaultVariant) {
                $variant = $variant->product->defaultVariant;
            }

            $stock = (float) $variant->stock;
            $step = (float) $variant->step;
            $qty = (float) $item->quantity;

            // ── 3. Нет в наличии
            if ($stock <= 0) {
                return [
                    'variant_id' => $item->variantId,
                    'price' => $variant->price,
                    'stock' => $stock,
                    'step' => $step,
                    'valid' => false,
                    'reason' => 'out_of_stock',
                ];
            }

            // ── 4. Превышает остаток
            if ($qty > $stock + self::EPSILON) {
                return [
                    'variant_id' => $item->variantId,
                    'price' => $variant->price,
                    'stock' => $stock,
                    'step' => $step,
                    'valid' => false,
                    'reason' => 'quantity_exceeded',
                ];
            }

            // ── 5. Не кратно шагу
            if ($step > 0) {
                $remainder = fmod($qty, $step);

                // Кратно, если remainder ≈ 0 или ≈ step (например fmod(0.5, 0.1) = 0.0999...)
                $isMultiple =
                    $remainder <= self::EPSILON ||
                    abs($remainder - $step) <= self::EPSILON;

                if (!$isMultiple) {
                    return [
                        'variant_id' => $item->variantId,
                        'price' => $variant->price,
                        'stock' => $stock,
                        'step' => $step,
                        'valid' => false,
                        'reason' => 'quantity_not_multiple',
                    ];
                }
            }

            // ── 6. Всё ок
            // Не «обрезаем» до stock — если quantity валиден, оставляем как есть.
            // Если бы quantity > stock, мы бы уже вышли на шаге 4.
            return [
                'variant_id' => $item->variantId,
                'valid' => true,
                'price' => $variant->price,
                'stock' => $stock,
                'step' => $step,
                'quantity' => $qty,
            ];
        })->values()->toArray();
    }
}
