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
        
        $comments = $page->reviews()
            ->latest()
            ->paginate(10);

        return Inertia::render('AboutView', [
            'page' => new PageResource($page),
            'comments' => CommentResource::collection($comments),
        ]);
    }

    public function delivery()
    {
        $page = Page::query()
            ->where('slug', 'delivery')
            ->where('is_active', true)
            ->with(['seo', 'media'])
            ->firstOrFail();

        $comments = $page->reviews()
            ->latest()
            ->paginate(10);

        return Inertia::render('Delivery/Index', [
            'page' => new PageResource($page),
            'comments' => CommentResource::collection($comments),

            // 🗺️ delivery-specific data
            'delivery' => [
                'farm_coords' => app(\App\Services\SettingService::class)->get('farm_coords'),
                'delivery_cost' => app(\App\Services\SettingService::class)->get('delivery_cost'),
                'free_delivery_from' => app(\App\Services\SettingService::class)->get('free_delivery_from'),
                'address_farm' => app(\App\Services\SettingService::class)->get('address_farm'),
            ],
        ]);
    }
}
