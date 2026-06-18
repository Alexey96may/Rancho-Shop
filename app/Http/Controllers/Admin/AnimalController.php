<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\Admin\AnimalResource as AdminAnimalResource;
use App\Enums\UserRole;
use App\Http\Requests\Admin\{StoreAnimalRequest};
use App\Models\Animal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use App\Models\Category;
use App\Traits\HandlesAdminMedia;

class AnimalController extends Controller
{
    use HandlesSmartPagination, HandlesAdminMedia;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'status']);

        $animals = Animal::query()
            ->with(['category', 'parent', 'seo'])
            ->orderBy('is_active', 'desc')
            ->withTrashControl($request, $filters)
            ->filter($filters)
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Animals/Index', [
            'animals' => AdminAnimalResource::collection($animals),
            'categories' => Category::where('type', 'animal')->get(['id', 'name', 'slug']),
            'filters' => $filters,
            'seo' => $this->seo('Панель управления: Животные', robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render('Admin/Animals/FormPage', [
            'animal' => null,
            'categories' => Category::where('type', 'animal')->get(['id', 'name', 'slug']),
            'seo' => $this->seo('Добавление новой особи', robots: 'noindex, nofollow'),
            'backUrl' => $request->query('back') 
                    ? route('admin.animals.index') . $request->query('back') 
                    : route('admin.animals.index'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnimalRequest $request)
    {
        $dto = $request->toDto();

        $animal = DB::transaction(function () use ($dto, $request) {
            $animal = Animal::create($dto->toArray());
            $animal->syncSeo($dto->seoData);
            
            $this->syncModelMedia($animal, $request);

            return $animal;
        });

        return $this->redirectWithFilters($request, 'admin.animals.index', "Животное «{$animal->name}» успешно добавлено!");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Animal $animal)
    {
        $animal->load(['seo', 'category', 'parent']);

        return Inertia::render('Admin/Animals/FormPage', [
            'animal' => new AdminAnimalResource($animal), 
            'categories' => Category::where('type', 'animal')->get(['id', 'name', 'slug']),
            'seo' => $this->seo("Редактирование {$animal->name}", robots: 'noindex, nofollow'),
            'backUrl' => $request->query('back') 
                ? route('admin.animals.index') . $request->query('back') 
                : route('admin.animals.index'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Animal $animal)
    {
        $dto = $request->toDto();

        DB::transaction(function () use ($dto, $request, $animal) {
            $animal->update($dto->toArray());
            $animal->syncSeo($dto->seoData);

            $this->syncModelMedia($animal, $request);
        });

        return $this->redirectWithFilters($request, 'admin.animals.index', "Данные животного «{$animal->name}» успешно обновлены!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id)
    {
        $animal = Animal::withTrashed()->findOrFail($id);

        if ($animal->trashed()) {
            Gate::authorize('forceDelete', $animal);

            $name = $animal->name;
            
            DB::transaction(function () use ($animal) {
                $animal->seo()?->delete();
                $animal->media()->delete();
                $animal->forceDelete();
            });

            return back()->with('success', "Животное «{$name}» окончательно удалено!");
        }

        Gate::authorize('delete', $animal);
        $animal->delete();
        
        return back()->with('success', "Животное «{$animal->name}» окончательно удалено!");
    }

    public function restore(Animal $animal)
    {
        Gate::authorize('restore', $animal);
        $animal->restore();

        return redirect()->back()->with('success', "Животное «{$animal->name}» успешно восстановлено из архива!");
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
