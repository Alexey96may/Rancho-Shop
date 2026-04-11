<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CommentResource;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
    * List of products for the catalog
    */
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->with(['category', 'media', 'variants.unit'])

            ->when(
                $request->category,
                fn ($q, $cat) =>
                    $q->whereHas('category', fn ($c) => $c->where('slug', $cat))
            )
            ->when(
                $request->search,
                fn ($q, $search) =>
                    $q->where('name', 'like', "%{$search}%")
            )
            ->when($request->sort === 'cheap', function ($q) {
                $q->withMin('variants', 'price')
                ->orderBy('variants_price_min', 'asc');
            })
            ->when($request->sort === 'expensive', function ($q) {
                $q->withMax('variants', 'price')
                ->orderBy('variants_price_max', 'desc');
            })

            ->when(!$request->sort, fn ($q) => $q->latest())
            ->get();

        $categories = Category::hasActiveProducts()->get();

        return Inertia::render('Catalog/Index', [
            'products' => ProductResource::collection($products),
            'filters' => $request->only(['category', 'search', 'sort']),
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load([
            'media',
            'seo',
            'category',
            'variants.unit',
            'comments' => fn($query) => $query->published()->latest()->with('user')
        ]);

        return Inertia::render('Catalog/Show', [
            'product' => new ProductResource($product),
            'comments' => CommentResource::collection($product->comments),
        ]);
    }
}
