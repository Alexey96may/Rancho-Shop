<?php

namespace App\Services;

use App\DTO\CheckoutDTO;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function handle(CheckoutDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {

            // 1. Получаем продукты
            // 2. Проверяем stock
            // 3. Создаём заказ
            // 4. Создаём order_items
            // 5. Списываем stock

            $products = \App\Models\Product::whereIn(
                'id',
                $dto->items->pluck('productId')
            )
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

            foreach ($dto->items as $item) {
                $product = $products->get($item->productId);

                if (!$product || !$product->isPurchasable($item->quantity)) {
                    throw new \Exception("Product {$item->productId} is not available");
                }
            }

            $totalPrice = 0;

            foreach ($dto->items as $item) {
                $product = $products->get($item->productId);

                $totalPrice += $product->price * $item->quantity;
            }

            $order = Order::create([
                'customer_name' => $dto->customerName,
                'customer_phone' => $dto->customerPhone,
                'delivery_address' => $dto->deliveryAddress,
                'customer_comment' => $dto->customerComment,

                'total_price' => $totalPrice,
                'delivery_price' => 0,
                'discount_total' => 0,

                'status' => 'new',
            ]);

            foreach ($dto->items as $item) {
                $product = $products->get($item->productId);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,

                    'price_at_purchase' => $product->price,
                    'base_price_at_purchase' => $product->price,

                    'quantity' => $item->quantity,
                ]);

                $product->decrement('stock', $item->quantity);
            }

            return $order->load('items');
        });
    }
}