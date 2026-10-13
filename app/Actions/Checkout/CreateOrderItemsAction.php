<?php

namespace App\Actions\Checkout;

use App\DTO\CheckoutDTO;
use App\Models\Order;
use Illuminate\Support\Collection;

class CreateOrderItemsAction
{
    public function handle(Order $order, CheckoutDTO $dto, Collection $products): void
    {
        // Собираем все вариации из загруженных продуктов в одну коллекцию
        $allVariants = $products->pluck('variants')->flatten();

        foreach ($dto->items as $item) {

            $variant = $allVariants->firstWhere('id', $item->variantId);

            if (!$variant) {
                continue;
            }

            // 2. Находим сам продукт из коллекции по product_id вариации
            $product = $products->get($variant->product_id);

            $order->items()->create([
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'product_name' => $product?->name ?? 'Товар',
                'unit_price' => $variant->price,
                'old_unit_price' => $variant->old_price,
                'quantity' => $item->quantity,
                'unit_name' => $variant->unit?->name,
                'unit_short' => $variant->unit?->short,
                'unit_code' => $variant->unit?->code,
            ]);
        }
    }
}
