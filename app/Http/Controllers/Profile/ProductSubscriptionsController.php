<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
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
}
