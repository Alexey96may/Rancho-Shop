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
            'products' => ProductResource::collection(Product::get()),
            
            'cows' => AnimalResource::collection(
                Animal::where('type', 'cow')->get()
            ),
            
            'about'  => new LandingBlockResource(LandingBlock::getSafe('about')),
            'values' => new LandingBlockResource(LandingBlock::getSafe('values')),
            'how_it_works' => new LandingBlockResource(LandingBlock::getSafe('how_it_works')),
            
            'faqs'   => FaqResource::collection(Faq::published()->orderBy('sort_order')->get()),
        ]);
    }
}
