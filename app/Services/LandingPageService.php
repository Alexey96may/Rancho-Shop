<?php

namespace App\Services;

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

class LandingPageService
{
    public function getData(): array
    {
        return [
            'products' => ProductResource::collection($this->getFeaturedProducts()),
            'cows' => AnimalResource::collection($this->getFeaturedAnimals()),
            'about' => new LandingBlockResource(LandingBlock::getSafe('about')),
            'values' => new LandingBlockResource(LandingBlock::getSafe('values')),
            'how_it_works' => new LandingBlockResource(LandingBlock::getSafe('how_it_works')),
            'comments' => CommentResource::collection($this->getFeaturedComments()),
            'faqs' => FaqResource::collection($this->getPublishedFaqs()),
        ];
    }

    private function getFeaturedProducts()
    {
        return Product::query()
            ->active()
            ->inStock()
            ->with(['defaultVariant.unit', 'category', 'media'])
            ->take(setting('featured_products_limit', 6))
            ->get();
    }

    private function getFeaturedAnimals()
    {
        return Animal::query()
            ->active()
            ->cows()
            ->take(setting('featured_animals_limit', 4))
            ->get();
    }

    private function getFeaturedComments()
    {
        return Comment::query()
            ->published()
            ->general()
            ->latest()
            ->take(setting('featured_comments_limit', 6))
            ->get();
    }

    private function getPublishedFaqs()
    {
        return Faq::published()->orderBy('sort_order')->get();
    }
}
