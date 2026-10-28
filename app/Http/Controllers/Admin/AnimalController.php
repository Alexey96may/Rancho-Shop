<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Animals\CreateAnimalAction;
use App\Actions\Admin\Animals\DeleteAnimalAction;
use App\Actions\Admin\Animals\UpdateAnimalAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnimalRequest;
use App\Http\Resources\Admin\AnimalResource as AdminAnimalResource;
use App\Models\Animal;
use App\Models\Category;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AnimalController extends Controller
{
    use HandlesSmartPagination;

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'status']);

        $animals = Animal::query()
            ->with(['category', 'parent', 'seo'])
            ->withTrashControl($request, $filters)
            ->filter($filters)
            ->sortAdmin()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Animals/Index', [
            'animals' => AdminAnimalResource::collection($animals),
            'categories' => Category::where('type', 'animal')->get(['id', 'name', 'slug']),
            'filters' => $filters,
            'seo' => $this->seo('Панель управления: Животные', robots: 'noindex, nofollow'),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Animals/FormPage', [
            'animal' => null,
            'categories' => Category::where('type', 'animal')->get(['id', 'name', 'slug']),
            'parents' => Animal::query()
                ->orderBy('name')
                ->get(['id', 'name', 'category_id']),
            'seo' => $this->seo('Добавление новой особи', robots: 'noindex, nofollow'),
            'backUrl' => $request->query('back')
                ? route('admin.animals.index') . $request->query('back')
                : route('admin.animals.index'),
        ]);
    }

    public function store(StoreAnimalRequest $request, CreateAnimalAction $action)
    {
        $animal = $action->handle($request->toDto(), $request);

        return $this->redirectWithFilters(
            $request,
            'admin.animals.index',
            "Животное «{$animal->name}» успешно добавлено!"
        );
    }

    public function edit(Request $request, Animal $animal)
    {
        $animal->load(['seo', 'category', 'parent']);

        return Inertia::render('Admin/Animals/FormPage', [
            'animal' => new AdminAnimalResource($animal),
            'categories' => Category::where('type', 'animal')->get(['id', 'name', 'slug']),
            'parents' => Animal::query()
                ->where('is_active', true)
                ->where('id', '!=', $animal->id)
                ->orderBy('name')
                ->get(['id', 'name', 'category_id']),
            'seo' => $this->seo("Редактирование {$animal->name}", robots: 'noindex, nofollow'),
            'backUrl' => $request->query('back')
                ? route('admin.animals.index') . $request->query('back')
                : route('admin.animals.index'),
        ]);
    }

    public function update(StoreAnimalRequest $request, Animal $animal, UpdateAnimalAction $action)
    {
        $action->handle($animal, $request->toDto($animal->id), $request);

        return $this->redirectWithFilters(
            $request,
            'admin.animals.index',
            "Данные животного «{$animal->name}» успешно обновлены!"
        );
    }

    public function destroy(Request $request, int $id, DeleteAnimalAction $action)
    {
        $animal = Animal::withTrashed()->findOrFail($id);
        $name = $animal->name;
        $wasTrashed = $animal->trashed();

        $action->handle($animal);

        $message = $wasTrashed
            ? "Животное «{$name}» окончательно удалено!"
            : "Животное «{$name}» перемещено в корзину";

        return back()->with('success', $message);
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
            ->when($request->current_id, fn ($q, $id) => $q->where('id', '!=', $id))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'category_id']);
    }

    public function deleteMedia(Animal $animal, Media $media)
    {
        if ($media->model_id !== $animal->id || $media->model_type !== $animal->getMorphClass()) {
            return redirect()->back()->with('error', 'Доступ запрещен');
        }
        $media->delete();

        return redirect()->back()->with('success', 'Фото удалено');
    }
}
