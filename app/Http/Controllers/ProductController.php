<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Resources\ProductResource;
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
            ->where('is_active', true)
            ->latest()
            ->get();

        return Inertia::render('Catalog/Index', [
            'products' => ProductResource::collection($products),
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
