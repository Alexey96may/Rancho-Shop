<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\Admin\AdminProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CommentResource;
use App\Models\Animal;
use App\Models\Category;
use App\Models\Product;
use App\Traits\HandlesAdminMedia;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ProductController extends Controller
{
    use HandlesAdminMedia, HandlesSmartPagination;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category', 'animal']);

        $products = Product::query()
            ->with(['category', 'variants.unit'])
            ->withCount(['variants', 'comments'])
            ->withAvg('comments', 'rating')
            ->withTrashControl($request, $filters)
            ->filter($filters)
            ->sortAdmin()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => AdminProductResource::collection($products),
            'filters' => $filters,
            'categories' => CategoryResource::collection(
                Category::forProducts()->orderBy('name')->get()
            ),
            'animals' => $this->getAnimalOptions(),
            'seo' => $this->seo('Панель управления: Продукты', 'Просмотр продуктов', robots: 'noindex, nofollow'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render('Admin/Products/Form', array_merge([
            'seo' => $this->seo('Создание продукта', robots: 'noindex, nofollow'),
            'backUrl' => $this->backUrl($request),
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

        return $this->redirectWithFilters(
            $request,
            'admin.products.index',
            "Товар «{$product->name}» создан!"
        );
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
                $product->comments()->with('user')->latest()->paginate(10)
            ),
            'backUrl' => $this->backUrl($request),
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

        return $this->redirectWithFilters(
            $request,
            'admin.products.index',
            "Товар «{$product->name}» обновлён!"
        );
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

            DB::transaction(function () use ($product) {
                $product->seo?->delete();
                $product->clearMediaCollection('main');
                $product->clearMediaCollection('gallery');
                $product->variants()->delete();
                $product->animals()->detach();
                $product->forceDelete();
            });

            return $this->redirectWithFilters(
                $request,
                'admin.products.index',
                "Товар «{$name}» окончательно удалён!"
            );
        }

        Gate::authorize('delete', $product);
        $product->delete();

        return $this->redirectWithFilters(
            $request,
            'admin.products.index',
            "Товар «{$product->name}» помечен как удалённый!"
        );
    }

    public function restore(Product $product): RedirectResponse
    {
        Gate::authorize('restore', $product);

        $product->restore();

        return back()->with('success', "Товар «{$product->name}» успешно восстановлен!");
    }

    /**
     * Общие опции форм create / edit.
     */
    private function getFormOptions(): array
    {
        return [
            'categories' => CategoryResource::collection(
                Category::forProducts()->orderBy('name')->get()
            ),
            'animals' => $this->getAnimalOptions(),
        ];
    }

    /**
     * Лёгкий список животных для select'ов — только id и name.
     */
    private function getAnimalOptions(): array
    {
        return Animal::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn ($animal) => [
                'id' => $animal->id,
                'name' => $animal->name,
            ])
            ->all();
    }

    /**
     * URL возврата с учётом фильтров.
     */
    private function backUrl(Request $request): string
    {
        return $request->query('back')
            ? route('admin.products.index') . $request->query('back')
            : route('admin.products.index');
    }
}
