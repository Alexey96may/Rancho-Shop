<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
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
        $variant = ProductVariant::whereHas('product', function ($q) {
            $q->where('slug', 'moloko-korovye-tselnoe');
        })->first();

        $promo = PromoCode::where('code', 'START2026')->first();

        if (!$variant) return;

        $quantity = 3;
        $deliveryPrice = 20000;
        $discount = $promo ? 30000 : 0; // 300 rub discount

        $unitPrice = $variant->price;

        // 1. Create main order
        $order = Order::create([
            'customer_name' => 'Алексей Тестовый',
            'customer_phone' => '+7 (978) 123-45-67',

            'delivery_address' => 'Симферополь, ул. Пушкина, 1',
            'delivery_lat' => 44.9521,
            'delivery_lng' => 34.1024,

            'is_pickup' => false,
            'delivery_validated' => true,

            'delivery_meta' => [
                'zone_id' => 1,
                'zone_name' => 'Simferopol center',
                'distance_km' => 2.8,
                'route_time_min' => 12,
                'pricing_rule' => 'base_v1',
            ],

            'customer_comment' => 'Пожалуйста, привезите до 12:00',

            'delivery_price' => $deliveryPrice,
            'discount_total' => $discount,
            
            // Total: (Price * Quantity) + Shipping - Discount
            'total_price' => ($unitPrice * $quantity) + $deliveryPrice - $discount,
            'status' => 'new',
            'promo_code_id' => $promo?->id,
        ]);

        // 2. Create a position within the order
        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'unit_price' => $unitPrice,
            'old_unit_price' => $variant->old_price,
            'unit_name' => $variant->unit?->name,
            'unit_code' => $variant->unit?->code,
            'quantity' => $quantity,
        ]);
    }
}
