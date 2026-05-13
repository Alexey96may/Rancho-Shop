<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\AdminProductResource;
use App\Http\Resources\{CommentResource, CategoryResource, AnimalResource, UnitResource};
use App\Models\{Product, Category, Animal, Unit};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Enums\AvailabilityType;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->withTrashed()
            ->with(['category', 'variants.unit'])
            ->withCount(['variants', 'comments']) 
            ->withAvg('comments', 'rating')
            ->filter($request->only(['search', 'category', 'animal']))
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();
        
        return Inertia::render('Admin/Products/Index', [
            'products' => AdminProductResource::collection($products),
            'filters' => $request->only(['search', 'category', 'animal']),
            'categories' => CategoryResource::collection(Category::forProducts()->orderBy('name')->get()),
            'animals' => AnimalResource::collection(Animal::orderBy('name')->get()),
            'seo' => $this->seo('Панель управления: Продукты', 'Просмотр продуктов',  robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Products/Form', array_merge([
            'seo' => $this->seo('Создание продукта', robots: 'noindex, nofollow'),
            ], $this->getFormOptions()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'required|exists:categories,id',
            'availability_type' => [
                'required',
                new Enum(AvailabilityType::class)
            ],
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'attributes' => 'nullable|array',
            'schedule' => 'nullable|array',
            'animal_ids' => 'array',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product = DB::transaction(function () use ($validated, $request) {
            $product = Product::create($validated);
            
            if ($request->has('animal_ids')) {
                $product->animals()->sync($request->animal_ids);
            }

            //Если прилетели медиа (Spatie)
            if ($request->hasFile('gallery')) {
                $product->addMultipleMediaFromRequest(['gallery'])
                    ->each(fn ($file) => $file->toMediaCollection('gallery'));
            }

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)
            ->with('success', "Продукт {$product->name} создан");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load(['variants.unit', 'category', 'media', 'animals', 'seo'])
                ->loadCount(['variants', 'comments'])
                ->loadAvg('comments', 'rating');

        return Inertia::render('Admin/Products/Form', array_merge([
            'product' => AdminProductResource::make($product),
            'seo' => $this->seo('Редактирование: ' . $product->name, robots: 'noindex, nofollow'),
            'comments' => CommentResource::collection(
                $product->comments()->latest()->paginate(10)
            ),
        ], $this->getFormOptions()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'availability_type' => [
                'required',
                new Enum(AvailabilityType::class)
            ],
            'description' => 'nullable|string',
            'attributes' => 'array',
            'schedule' => 'array',
            'animal_ids' => 'array',
        ]);

        $validated['slug'] = empty($validated['slug']) 
            ? Str::slug($validated['name']) 
            : Str::slug($validated['slug']);

        DB::transaction(function () use ($validated, $request, $product) {
            $product->update($validated);

            if ($request->has('animal_ids')) {
                $product->animals()->sync($request->animal_ids);
            }
            
            if ($request->has('remove_media')) {
                Media::whereIn('id', $request->remove_media)->delete();
            }

            if ($request->hasFile('gallery')) {
                $product->addMultipleMediaFromRequest(['gallery'])
                    ->each(fn ($file) => $file->toMediaCollection('gallery'));
            }
        });

        return back()->with('success', "Продукт {$product->name} обновлен");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete(); // Soft Delete

        return redirect()->route('admin.products.index')
            ->with('success', "Продукт {$product->name}  отправлен в корзину");
    }

    public function restore(int $id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return back()->with('success', "Продукт {$product->name} восстановлен");
    }

    private function getFormOptions()
    {
        return [
            'categories' => CategoryResource::collection(Category::forProducts()->orderBy('name')->get()),
            'animals'    => AnimalResource::collection(Animal::select('id', 'name')->get()),
        ];
    }
    
}
