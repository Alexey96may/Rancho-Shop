<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProductSubscriptionsController extends Controller
{
    public function index(Request $request): Response
    {
        $products = $request->user()
            ->subscribedProducts()
            ->with(['defaultVariant', 'defaultVariant.unit', 'category', 'seo'])
            ->withCount('variants')
            ->latest('product_subscriptions.created_at')
            ->paginate(setting('products_per_page', 12));

        return Inertia::render('Profile/Subscriptions/Index', [
            'products' => ProductResource::collection($products),
        ]);
    }

    public function store(Product $product)
    {
        $user = Auth::user();

        $user->subscribedProducts()->syncWithoutDetaching([$product->id]);

        if (!$user->email) {
            return redirect()
                ->route('catalog.show', $product->slug)
                ->with('warning', 'Вы подписаны, но у вас не указан email. Добавьте его в профиле, чтобы получать уведомления о поступлении товара.')
                ->setStatusCode(303);
        }

        return redirect()
            ->route('catalog.show', $product->slug)
            ->with('success', 'Вы подписаны на уведомления о продукте.');
    }

    public function destroy(Product $product)
    {
        Auth::user()->subscribedProducts()->detach($product->id);

        return redirect()
            ->route('catalog.show', $product->slug)
            ->with('success', 'Подписка на продукт отменена')
            ->setStatusCode(303);
    }
}
