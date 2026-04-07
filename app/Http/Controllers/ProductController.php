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
            ->with(['category', 'media'])

            ->when($request->category, fn($q, $cat) => $q->whereHas('category', fn($c) => $c->where('slug', $cat)))
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->sort === 'cheap', fn($q) => $q->orderBy('price', 'asc'))
            ->when($request->sort === 'expensive', fn($q) => $q->orderBy('price', 'desc'))

            ->latest()
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
            'comments' => fn($query) => $query->published()->latest()->with('user')
        ]);

        return Inertia::render('Catalog/Show', [
            'product' => new ProductResource($product),
            'comments' => CommentResource::collection($product->comments),
        ]);
    }
}
