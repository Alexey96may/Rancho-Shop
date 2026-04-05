<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Resources\AnimalResource;
use App\Http\Resources\LandingBlockResource;
use App\Http\Resources\FaqResource;
use App\Models\Product;
use App\Models\Animal;
use App\Models\LandingBlock;
use App\Models\Faq;

class ApiLandingController extends Controller
{
    public function index()
    {
        return [
            'products' => ProductResource::collection(Product::with('media')->get()),
            'cows'     => AnimalResource::collection(Animal::with('media')->where('type', 'cow')->get()),
            'hero'     => new LandingBlockResource(LandingBlock::getSafe('hero')),
            'values'   => new LandingBlockResource(LandingBlock::getSafe('values')),
            'faqs'     => FaqResource::collection(Faq::published()->get()),
        ];
    }
}
