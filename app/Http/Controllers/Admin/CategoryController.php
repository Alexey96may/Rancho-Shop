<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('sort_order')
            ->paginate(setting('categories_per_page', 12))
            ->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => CategoryResource::collection($categories),
            'filters' => $request->only(['search', 'type'])
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
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:categories,slug',
            'icon'       => 'nullable|string|max:255',
            'type'       => 'required|string|in:product,animal',
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ]);

        // Авто-генерация слага, если не указан
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория успешно создана');
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
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'slug'       => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'icon'       => 'nullable|string|max:255',
            'type'       => 'required|string|in:product,animal',
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Категория обновлена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Проверяем, нет ли в категории товаров или животных перед удалением
        if ($category->products()->count() > 0 || $category->animals()->count() > 0) {
            return redirect()->back()->with('error', 'Нельзя удалить категорию, в которой есть товары или животные');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория удалена');
    }
}
