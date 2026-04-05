<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Animal;
use App\Models\LandingBlock;
use App\Http\Resources\ProductResource;
use App\Models\Faq;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        return Inertia::render('HomeView', [
            'products' => ProductResource::collection(Product::with('media')->get()),
            
            'cows' => Animal::with('media')->cows()->get()->map(function ($animal) {
                $mapped = $this->mapMedia($animal);
                $voiceMedia = $animal->media->first(fn($m) => str_contains($m->mime_type, 'audio'));
                
                $mapped['voice_url'] = $voiceMedia ? $voiceMedia->getUrl() : null;
                return $mapped;
            }),
            
            'hero'   => LandingBlock::getByKey('hero'),
            'values' => LandingBlock::getByKey('values'),
            'faqs'   => Faq::published()->get(),
        ]);
    }

    /**
    * Helper for media mapping (Spatie Media Library)
    */
    protected function mapMedia($model)
    {
        $data = $model->toArray();
        $data['media'] = $model->media->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->getUrl(),
            
        ]);

        return $data;
    }
}
