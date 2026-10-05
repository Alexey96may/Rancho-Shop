<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use App\Http\Requests\Admin\CategoryRequest;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use HandlesSmartPagination;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type']);

        $categories = Category::query()
            ->filter($filters)
            ->orderBy('is_active', 'desc')
            ->orderBy('sort_order')
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => CategoryResource::collection($categories),
            'filters' => $filters,
            'seo' => $this->seo('Панель управления: Категории', robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $dto = $request->toDto();
        $category = Category::create($dto->toArray());

        return redirect()->route('admin.categories.index')
            ->with('success', "Категория «{$category->name}» успешно создана");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $dto = $request->toDto();
        $category->update($dto->toArray());

        return redirect()->back()->with('success', "Категория $category->name обновлена");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category)
    {
        if ($category->products()->count() > 0 || $category->animals()->count() > 0) {
            return redirect()->back()->with('error', 'Нельзя удалить категорию, в которой есть товары или животные');
        }

        $category->delete();

        return $this->redirectWithFilters($request, 'admin.categories.index', "Категория «{$category->name}» удалена!");
    }
}
