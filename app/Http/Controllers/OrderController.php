<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Валидация структуры
        $cartItems = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // 2. Бизнес-логика (Сервис, о котором мы говорили!)
        // Здесь проверяем актуальные цены в БД и наличие stock
        
        return back()->with('success', 'Заказ принят!');
    }
}
