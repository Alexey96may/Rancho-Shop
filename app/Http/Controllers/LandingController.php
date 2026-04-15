<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Animal;
use App\Models\LandingBlock;
use App\Models\Comment;
use App\Models\Setting;
use App\Http\Resources\AnimalResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\LandingBlockResource;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        $limit = Setting::where('key', 'featured_comments_limit')->value('value') ?? 6;

        return Inertia::render('HomeView', [
            'products' => ProductResource::collection(Product::with(['variants', 'category', 'media'])->get()),
            
            'cows' => AnimalResource::collection(
                Animal::where('type', 'cow')->get()
            ),
            
            'about'  => new LandingBlockResource(LandingBlock::getSafe('about')),
            'values' => new LandingBlockResource(LandingBlock::getSafe('values')),
            'how_it_works' => new LandingBlockResource(LandingBlock::getSafe('how_it_works')),
            'comments' => CommentResource::collection(
                Comment::where('is_published', true)
                        // ->where('commentable_type', 'page')
                        ->latest()
                        ->take($limit) 
                        ->get()
            ),
            
            'faqs'   => FaqResource::collection(Faq::published()->orderBy('sort_order')->get()),
        ]);
    }
}
