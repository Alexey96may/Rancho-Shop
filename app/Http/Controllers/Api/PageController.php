<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['seo', 'media'])
            ->firstOrFail();

        $reviews = $page->reviews()
            ->latest()
            ->paginate(10);

        return response()->json([
            'page' => new PageResource($page),

            'reviews' => [
                'data' => CommentResource::collection($reviews),
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
            ],
        ]);
    }
}