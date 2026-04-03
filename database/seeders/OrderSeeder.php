<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = Product::where('slug', 'moloko-korovye-tselnoe')->first();
        $promo = PromoCode::where('code', 'START2026')->first();

        if ($product) {
            $quantity = 3;
            $deliveryPrice = 20000; 
            $discount = $promo ? 30000 : 0; // 300 rub discount

            // 1. Create main order
            $order = Order::create([
                'customer_name' => 'Алексей Тестовый',
                'customer_phone' => '+7 (978) 123-45-67',
                'delivery_address' => 'Симферополь, ул. Пушкина, 1',
                'customer_comment' => 'Пожалуйста, привезите до 12:00',
                'delivery_price' => $deliveryPrice,
                'discount_total' => $discount,
                // Total: (Price * Quantity) + Shipping - Discount
                'total_price' => ($product->price * $quantity) + $deliveryPrice - $discount,
                'status' => 'new',
                'promo_code_id' => $promo?->id,
            ]);

            // 2. Create a position within the order
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price_at_purchase' => $product->price,
                'base_price_at_purchase' => $product->price,
                'quantity' => $quantity,
            ]);
        }
    }
}
