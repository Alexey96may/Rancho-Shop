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
        $filters = $request->only(['category', 'search', 'sort', 'animal', 'in_stock']);

        $products = Product::query()
            ->with([
                'category',
                'media',
                'defaultVariant.unit',
            ])
            ->filter($filters)
            ->paginate(setting('products_per_page', 12))
            ->withQueryString();

        $categories = Category::hasActiveProducts()->get();

        return Inertia::render('Catalog/Index', [
            'products' => ProductResource::collection($products),
            'categories' => CategoryResource::collection($categories),
            'filters' => $filters,
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load([
            'media',
            'seo',
            'category',
            'defaultVariant.unit',
        ]);

        $comments = $product->comments()
            ->published()
            ->latest()
            ->paginate(setting('products_per_page', 8));

        return Inertia::render('Catalog/Show', [
            'product' => new ProductResource($product),
            'comments' => [
                'data' => CommentResource::collection($comments->items())->resolve(),
                'meta' => [
                    'current_page' => $comments->currentPage(),
                    'last_page' => $comments->lastPage(),
                ],
            ],
        ]);
    }
}
