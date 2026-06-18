<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\AdminProductResource;
use App\Http\Resources\{CommentResource, CategoryResource, AnimalResource};
use App\Http\Requests\Admin\{StoreProductRequest, UpdateProductRequest};
use App\Models\{Product, Category, Animal};
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use App\Traits\HandlesAdminMedia;
use Illuminate\Support\Facades\Gate;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    use HandlesSmartPagination, HandlesAdminMedia;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category', 'animal', 'status']);
        
        $products = Product::query()
            ->with(['category', 'variants.unit'])
            ->withCount(['variants', 'comments']) 
            ->withAvg('comments', 'rating')
            ->withTrashControl($request, $filters)
            ->filter($filters)
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();
        
        return Inertia::render('Admin/Products/Index', [
            'products' => AdminProductResource::collection($products),
            'filters' => $filters,
            'categories' => CategoryResource::collection(Category::forProducts()->orderBy('name')->get()),
            'animals' => AnimalResource::collection(Animal::orderBy('name')->get()),
            'seo' => $this->seo('Панель управления: Продукты', 'Просмотр продуктов',  robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render('Admin/Products/Form', array_merge([
            'seo' => $this->seo('Создание продукта', robots: 'noindex, nofollow'),
            'backUrl' => $request->query('back') 
                    ? route('admin.products.index') . $request->query('back') 
                    : route('admin.products.index'),
            ], $this->getFormOptions()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $dto = $request->toDto();

        $product = DB::transaction(function () use ($dto, $request) {
            $product = Product::create($dto->toArray());
            
            $product->animals()->sync($dto->animal_ids);  
            $product->syncSeo($dto->seoData);
            
            $this->syncModelMedia($product, $request);

            return $product;
        });
        
        return $this->redirectWithFilters($request, 'admin.products.index', "Товар «{$product->name}» создан!");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Product $product)
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
            'backUrl' => $request->query('back') 
                ? route('admin.products.index') . $request->query('back') 
                : route('admin.products.index'),
        ], $this->getFormOptions()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $dto = $request->toDto();

        DB::transaction(function () use ($dto, $request, $product) {
            $product->update($dto->toArray());
            
            $product->animals()->sync($dto->animal_ids);
            $product->syncSeo($dto->seoData);
            
            $this->syncModelMedia($product, $request);
        });

        return $this->redirectWithFilters($request, 'admin.products.index', "Товар «{$product->name}» обновлён!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->trashed()) {
            Gate::authorize('forceDelete', $product);

            $name = $product->name;
            $product->forceDelete();

            return back()->with('success', "Товар «{$name}» окончательно удалён!");
        }

        Gate::authorize('delete', $product); // ProductPolicy@delete
        $product->delete();
        
        return $this->redirectWithFilters($request, 'admin.products.index', "Товар «{$product->name}» помечен как удалённый!");
    }

    public function restore(Product $product): RedirectResponse
    {
        Gate::authorize('restore', $product);

        $product->restore();

        return back()->with('success', "Товар «{$product->name}» успешно восстановлен!");
    }

    private function getFormOptions()
    {
        return [
            'categories' => CategoryResource::collection(Category::forProducts()->orderBy('name')->get()),
            'animals'    => AnimalResource::collection(Animal::select('id', 'name')->get()),
        ];
    }
    
}
