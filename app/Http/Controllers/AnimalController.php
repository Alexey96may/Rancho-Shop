<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnimalResource;
use App\Models\Animal;
use Inertia\Inertia;

class AnimalController extends Controller
{
    public function index()
    {
        $animals = Animal::query()
            ->with(['media', 'seo'])
            ->latest()
            ->get();

        return Inertia::render('Animals/Index', [
            'animals' => AnimalResource::collection($animals),
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

        return Inertia::render('Animals/Show', [
            'animal' => new AnimalResource($animal),
        ]);
    }
}
