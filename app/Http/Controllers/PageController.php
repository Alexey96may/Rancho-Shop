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
            ->paginate(setting('comments_per_page', 8));

        return Inertia::render('AboutView', [
            'page' => new PageResource($page),
            'comments' => CommentResource::collection($comments),
        ]);
    }

    public function delivery()
    {
        $settings = app(\App\Services\SettingService::class);

        $page = Page::query()
            ->where('slug', 'delivery')
            ->where('is_active', true)
            ->with(['seo', 'media'])
            ->firstOrFail();

        $comments = $page->reviews()
            ->latest()
            ->paginate(setting('comments_per_page', 8));

        return Inertia::render('Delivery/Index', [
            'page' => new PageResource($page),
            'comments' => CommentResource::collection($comments),

            // 🗺️ delivery-specific data
            'delivery' => [
                'farm_coords' => $this->parseCoords($settings->get('farm_coords')),
                'delivery_schedule' => $settings->get('delivery_schedule'),
                'delivery_info' => $settings->get('delivery_info'),
                'delivery_zones' => $settings->get('delivery_zones'),
                'address_farm' => $settings->get('address_farm'),
            ],
        ]);
    }

    private function parseCoords(?string $value): ?array
    {
        if (!$value) return null;

        [$lat, $lng] = explode(',', $value);

        return [
            'lat' => (float) $lat,
            'lng' => (float) $lng,
        ];
    }
}
