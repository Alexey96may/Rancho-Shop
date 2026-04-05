<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Animal;
use App\Models\LandingBlock;
use App\Http\Resources\AnimalResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\LandingBlockResource;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        return Inertia::render('HomeView', [
            'products' => ProductResource::collection(Product::with('media')->get()),
            
            'cows' => AnimalResource::collection(
                Animal::with('media')->where('type', 'cow')->get()
            ),
            
            'hero'   => new LandingBlockResource(LandingBlock::getByKey('hero')),
            'values' => new LandingBlockResource(LandingBlock::getByKey('values')),
            'faqs'   => FaqResource::collection(Faq::published()->orderBy('sort_order')->get()),
        ]);
    }
}
