<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Получение списка товаров и фильтров для каталога
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'media'])
            ->when($request->category, fn($q, $cat) => $q->whereHas('category', fn($c) => $c->where('slug', $cat)))
            ->when($request->search, fn($q, $search) => $q->where('name', 'ilike', "%{$search}%"))
            ->when($request->sort === 'cheap', fn($q) => $q->orderBy('price', 'asc'))
            ->when($request->sort === 'expensive', fn($q) => $q->orderBy('price', 'desc'))
            ->latest()
            ->paginate(12);

        $categories = Category::hasActiveProducts()->get();

        return response()->json([
            'products'   => ProductResource::collection($products)->response()->getData(true),
            'categories' => CategoryResource::collection($categories),
            'filters'    => $request->only(['category', 'search', 'sort']),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load([
            'media',
            'seo',
            'category', 
            'comments' => fn($query) => $query->published()->latest()->with('user')
        ]);

        return response()->json([
            'product'  => new ProductResource($product),
            'comments' => CommentResource::collection($product->comments),
        ]);
    }
}