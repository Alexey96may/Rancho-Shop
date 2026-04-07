<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CommentResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with(['category', 'media'])
            ->where('is_active', true)
            ->latest()
            ->get();

        return ProductResource::collection($products);
    }

    public function show(Product $product): array
    {
        $product->load([
            'media',
            'seo',
            'category', 
            'comments' => fn($query) => $query->published()->latest()->with('user')
        ]);

        return [
            'product'  => new ProductResource($product),
            'comments' => CommentResource::collection($product->comments),
        ];
    }
}