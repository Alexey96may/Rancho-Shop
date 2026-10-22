<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductSubscriptionController extends Controller
{
    public function store(Product $product)
    {
        Auth::user()->subscribedProducts()->syncWithoutDetaching([$product->id]);

        return back()->with('success', 'Вы подписаны на уведомления');
    }

    public function destroy(Product $product)
    {
        Auth::user()->subscribedProducts()->detach($product->id);

        return back()->with('success', 'Подписка отменена');
    }
}
