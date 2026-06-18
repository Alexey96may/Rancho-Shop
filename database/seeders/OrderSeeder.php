<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = ProductVariant::with(['product', 'unit'])->get();
        $promo = PromoCode::where('code', 'START2026')->first();

        if ($variants->isEmpty()) {
            $this->command->error('First, fill out the product options table!');
            return;
        }

        for ($i = 0; $i < 30; $i++) {
            // Generate a random date in the past
            $date = Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23));
            
            // Select 1–3 random products for this order
            $orderVariants = $variants->random(rand(1, 3));
            
            $deliveryPrice = 20000;
            $discount = (rand(0, 10) > 7) ? 30000 : 0; // 30% chance for a discount
            
            $itemsTotal = 0;
            $itemsCount = 0;

            // 1. Create an empty order to obtain an ID (we will calculate the total later)
            $order = Order::create([
                'customer_name' => 'Тестовый Клиент #' . ($i + 1),
                'customer_phone' => '+7 (978) ' . rand(100, 999) . '-' . rand(10, 99) . '-' . rand(10, 99),
                'delivery_address' => 'г. Симферополь, ул. Тестовая, ' . rand(1, 100),
                'is_pickup' => false,
                'delivery_price' => $deliveryPrice,
                'discount_total' => $discount,
                'status' => 'completed', // Set to 'Completed' immediately so that analytics can track it.
                'promo_code_id' => $discount > 0 ? $promo?->id : null,
                'created_at' => $date,
                'updated_at' => $date,
                'total_price' => 0, // Update later
            ]);

            // 2. Create order items
            foreach ($orderVariants as $variant) {
                $qty = rand(1, 5);
                $subTotal = $variant->price * $qty;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'unit_price' => $variant->price,
                    'unit_name' => $variant->unit?->name,
                    'quantity' => $qty,
                    'created_at' => $date,
                ]);

                $itemsTotal += $subTotal;
                $itemsCount += $qty;
            }

            // 3. Update the final order total
            $order->update([
                'total_price' => $itemsTotal + $deliveryPrice - $discount
            ]);

            $analytics = app(\App\Services\Admin\AnalyticsService::class);
            $analytics->updateStats($order);
        }
    }
}
