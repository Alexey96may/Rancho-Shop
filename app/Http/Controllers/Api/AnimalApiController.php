<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnimalResource;
use App\Models\Animal;
use Illuminate\Http\Request;

class AnimalApiController extends Controller
{
    public function index(Request $request)
    {
        $animals = Animal::query()
            ->with(['media'])
            ->latest()
            ->paginate(24);

        return AnimalResource::collection($animals);
    }

    public function show(Animal $animal)
    {
        $animal->load([
            'media',
            'parent',
            'children',
            'seo',
        ]);

        return new AnimalResource($animal);
    }
}
