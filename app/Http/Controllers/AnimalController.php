<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnimalResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CommentResource;
use App\Models\Animal;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnimalController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'status']);

        $animals = Animal::query()
            ->with(['media', 'seo', 'category'])
            ->filter($filters)
            ->sortPublic()
            ->paginate(setting('animals_per_page', 12))
            ->withQueryString();

        $categories = Category::query()
            ->whereHas('animals')
            ->get();

        $statuses = Animal::query()
            ->whereNotNull('status')
            ->distinct()
            ->pluck('status')
            ->map(fn ($status) => [
                'id' => $status,
                'name' => ucfirst($status),
            ]);

        return Inertia::render('Animals/Index', [
            'animals' => AnimalResource::collection($animals),
            'categories' => CategoryResource::collection($categories),
            'statuses' => $statuses,
            'filters' => $filters,
            'seo' => $this->seo('Наши жители фермы', 'Познакомьтесь с животными, которые живут на нашей ферме'),
        ]);
    }

    public function show(Animal $animal)
    {
        $animal->load([
            'media',
            'parent',
            'children',
            'seo',
        ]);

        $comments = $animal->comments()
            ->latest()
            ->paginate(setting('animals_per_page', 8));

        return Inertia::render('Animals/Show', [
            'animal' => new AnimalResource($animal),
            'comments' => [
                'data' => CommentResource::collection($comments->items())->resolve(),
                'meta' => [
                    'current_page' => $comments->currentPage(),
                    'last_page' => $comments->lastPage(),
                    'per_page' => $comments->perPage(),
                    'total' => $comments->total(),
                    'links' => $comments->linkCollection()->toArray(),
                ],
            ],
            'seo' => $this->seoFromModel($animal, $animal->name),
        ]);
    }
}
