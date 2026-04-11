<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $items = $request->validated()['items'];

        $total = 0;

        foreach ($items as $item) {

            $variant = ProductVariant::with('product')
                ->findOrFail($item['variant_id']);

            // ⚠️ всегда берём актуальную цену из БД
            $price = $variant->price;

            // проверка stock
            if ($variant->stock < $item['quantity']) {
                return back()->withErrors([
                    'stock' => "Недостаточно товара: {$variant->name}"
                ]);
            }

            $total += $price * $item['quantity'];
        }

        $order = Order::create([
            'total_price' => $total,
            'status' => 'new',
        ]);
        
        return back()->with('success', 'Заказ принят!');
    }
}
