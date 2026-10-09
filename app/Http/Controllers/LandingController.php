<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnimalResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\FaqResource;
use App\Http\Resources\LandingBlockResource;
use App\Http\Resources\ProductResource;
use App\Models\Animal;
use App\Models\Comment;
use App\Models\Faq;
use App\Models\LandingBlock;
use App\Models\Product;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        return Inertia::render('HomeView', [
            'products' => ProductResource::collection(
                Product::query()
                    ->active()
                    ->inStock()
                    ->with(['defaultVariant.unit', 'category', 'media'])
                    ->take(setting('featured_products_limit', 6))
                    ->get()
            ),
            'cows' => AnimalResource::collection(
                Animal::query()
                    ->active()
                    ->cows()
                    ->take(setting('featured_animals_limit', 4))
                    ->get()
            ),
            'about' => new LandingBlockResource(LandingBlock::getSafe('about')),
            'values' => new LandingBlockResource(LandingBlock::getSafe('values')),
            'how_it_works' => new LandingBlockResource(LandingBlock::getSafe('how_it_works')),
            'comments' => CommentResource::collection(
                Comment::query()
                    ->published()
                    ->general()
                    ->latest()
                    ->take(setting('featured_comments_limit', 6))
                    ->get()
            ),
            'faqs' => FaqResource::collection(Faq::published()->orderBy('sort_order')->get()),
            'seo' => $this->seo('Молочная Долина', 'Магазин - семейное Ранчо'),
        ]);
    }
}
