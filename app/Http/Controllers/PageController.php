<?php

namespace App\Http\Controllers;

use App\Http\Resources\PageResource;
use App\Http\Resources\CommentResource;
use App\Models\Page;
use Inertia\Inertia;

class PageController extends Controller
{
    public function about()
    {
        $page = Page::query()
            ->where('slug', 'about')
            ->where('is_active', true)
            ->with(['seo', 'media'])
            ->firstOrFail();
        
        $reviews = $page->reviews()
            ->latest()
            ->paginate(10);

        return Inertia::render('AboutView', [
            'page' => new PageResource($page),
            'reviews' => CommentResource::collection($reviews),
        ]);
    }
}
