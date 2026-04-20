<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\Admin\AnimalResource as AdminAnimalResource;
use App\Models\Animal;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;
use App\Models\Category;

class AnimalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $animals = Animal::query()
            ->with(['category', 'parent', 'seo'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->category_id, function ($query, $catId) {
                // For ID
                if (is_numeric($catId)) {
                    $query->where('category_id', $catId);
                } 
                // For Slug 
                else {
                    $query->whereHas('category', function($q) use ($catId) {
                        $q->where('slug', $catId);
                    });
                }
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(setting('per_page_animals', 12))
            ->withQueryString();

        return Inertia::render('Admin/Animals/Index', [
            'animals' => AdminAnimalResource::collection($animals),
            'categories' => Category::where('type', 'animal')->get(['id', 'name', 'slug']),
            'filters' => $request->only(['search', 'category_id', 'status'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'parent_id'   => 'nullable|exists:animals,id',
            'status'      => 'required|string',
            'bio'         => 'nullable|string',
            'features'    => 'nullable|array',
            'is_active'   => 'boolean',
            'avatar'      => 'nullable|image|max:2048', // 2MB max
            'voice'       => 'nullable|mimes:mp3,wav|max:5120', // 5MB max
            'gallery'     => 'nullable|array',
            'gallery.*'   => 'image|mimes:jpeg,png,jpg,webp|max:3072',

            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $animal = Animal::create($validated);

        if ($request->has('seo')) {
            $animal->seo()->create($request->input('seo'));
        }

        // Работа с медиа
        if ($request->hasFile('avatar')) {
            $animal->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $animal->addMedia($file)->toMediaCollection('gallery');
            }
        }

        if ($request->hasFile('voice')) {
            $animal->addMediaFromRequest('voice')->toMediaCollection('voice');
        }

        return redirect()->route('admin.animals.index')->with('success', 'Животное успешно добавлено');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'parent_id'   => 'nullable|exists:animals,id',
            'status'      => 'required|string',
            'bio'         => 'nullable|string',
            'features'    => 'nullable|array',
            'is_active'   => 'boolean',
            'avatar'      => 'nullable|image|max:2048', // 2MB max
            'voice'       => 'nullable|mimes:mp3,wav|max:5120', // 5MB max
            'gallery'     => 'nullable|array',
            'gallery.*'   => 'image|mimes:jpeg,png,jpg,webp|max:3072',

            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
        ]);

        $animal->update($validated);

        if ($request->has('seo')) {
            $animal->seo()->updateOrCreate(
                ['seoable_id' => $animal->id, 'seoable_type' => Animal::class],
                $request->input('seo')
            );
        }

        if ($request->hasFile('avatar')) {
            $animal->clearMediaCollection('avatars');
            $animal->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        if ($request->hasFile('gallery')) {
            // If you need to delete the OLD gallery before loading a new one, uncomment:
            // $animal->clearMediaCollection('gallery');

            foreach ($request->file('gallery') as $file) {
                $animal->addMedia($file)->toMediaCollection('gallery');
            }
        }

        if ($request->hasFile('voice')) {
            $animal->addMediaFromRequest('voice')->toMediaCollection('voice');
        }

        return redirect()->back()->with('success', 'Данные животного обновлены');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Animal $animal)
    {
        $animal->delete();
        return redirect()->route('admin.animals.index')->with('success', 'Животное перемещено в архив');
    }

    public function restore($id)
    {
        $animal = Animal::withTrashed()->findOrFail($id);
        $animal->restore();

        return redirect()->back()->with('success', 'Животное восстановлено из архива');
    }

    public function getPotentialParents(Request $request)
    {
        return Animal::query()
            ->where('id', '!=', $request->current_id)
            ->where('category_id', $request->category_id)
            ->get(['id', 'name']);
    }

    public function deleteMedia(Animal $animal, Media $media)
    {
        if ($media->model_id !== $animal->id || $media->model_type !== Animal::class) {
            return redirect()->back()->with('error', 'Доступ запрещен');
        }
        $media->delete();

        return redirect()->back()->with('success', 'Фото удалено');
    }
}
