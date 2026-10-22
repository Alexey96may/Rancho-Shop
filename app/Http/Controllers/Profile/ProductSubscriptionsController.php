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
        $user = $request->user();

        $query = $user->subscribedProducts();

        $total = (clone $query)->count();

        $inStock = (clone $query)
            ->whereHas('defaultVariant', function ($q) {
                $q->where('stock', '>', 0);
            })
            ->count();

        $products = $query
            ->with(['defaultVariant', 'defaultVariant.unit', 'category', 'seo'])
            ->withCount('variants')
            ->latest('product_subscriptions.created_at')
            ->paginate(setting('products_per_page', 12));

        return Inertia::render('Profile/Subscriptions/Index', [
            'products' => ProductResource::collection($products),
            'counts' => [
                'total' => $total,
                'in_stock' => $inStock,
            ],
        ]);
    }

    public function store(Product $product)
    {
        $user = Auth::user();

        $user->subscribedProducts()->syncWithoutDetaching([$product->id]);

        if (!$user->email) {
            return redirect()
                ->route('catalog.show', $product->slug)
                ->with('warning', 'Вы подписаны, но у вас не указан email. Добавьте его в профиле, чтобы получать уведомления о поступлении товара.');
        }

        return redirect()
            ->route('catalog.show', $product->slug)
            ->with('success', 'Вы подписаны на уведомления о продукте.')
            ->setStatusCode(302);
    }

    public function destroy(Product $product)
    {
        $user = Auth::user();
        $user->subscribedProducts()->detach($product->id);

        return back()->with('success', 'Подписка на продукт отменена');
    }
}
