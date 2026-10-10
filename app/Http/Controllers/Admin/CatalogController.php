<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CatalogRequest;
use App\Http\Requests\Admin\QuickUpdateCatalogRequest;
use App\Http\Resources\Admin\AdminProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    use HandlesSmartPagination;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'product_id', 'unit_id', 'in_stock', 'sort']);

        $variants = ProductVariant::query()
            ->with(['product.media', 'unit'])
            ->filter($filters)
            ->sort($filters['sort'] ?? null)
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Catalog/Index', [
            'variants' => AdminProductVariantResource::collection($variants),
            'products' => Product::select(['id', 'name'])->orderBy('name')->get(),
            'units' => Unit::select(['id', 'name'])->get(),
            'filters' => $filters,
            'sortOptions' => $this->getSortOptions(),
            'seo' => $this->seo('Панель управления: Прайс-лист', 'Просмотр вариантов товаров', robots: 'noindex, nofollow'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render('Admin/Catalog/Form', [
            'products' => Product::select(['id', 'name'])->orderBy('name')->get(),
            'units' => Unit::select(['id', 'short', 'name'])->orderBy('name')->get(),
            'seo' => $this->seo('Создание нового варианта товара', robots: 'noindex, nofollow'),
            'backUrl' => $request->query('back')
                ? route('admin.catalog.index') . $request->query('back')
                : route('admin.catalog.index'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CatalogRequest $request)
    {
        $dto = $request->toDto();
        $variant = ProductVariant::create($dto->toArray());

        if ($request->boolean('create_another')) {
            return redirect()->back()
                ->with('success', "Вариант «{$variant->name}» создан. Можете добавить следующий.");
        }

        return $this->redirectWithFilters($request, 'admin.catalog.index', "Вариант «{$variant->name}» успешно создан!");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductVariant $catalog, Request $request)
    {
        $catalog->load(['product.media', 'unit']);

        return Inertia::render('Admin/Catalog/Form', [
            'products' => Product::select(['id', 'name'])->orderBy('name')->get(),
            'variant' => new AdminProductVariantResource($catalog),
            'units' => Unit::select(['id', 'short', 'name'])->orderBy('name')->get(),
            'seo' => $this->seo('Редактирование варианта: ' . $catalog->name, robots: 'noindex, nofollow'),
            'isEdit' => true,
            'backUrl' => $request->query('back') ? route('admin.catalog.index') . $request->query('back') : route('admin.catalog.index'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CatalogRequest $request, ProductVariant $catalog)
    {
        $dto = $request->toDto();
        $catalog->update($dto->toArray());

        return $this->redirectWithFilters($request, 'admin.catalog.index', "Вариант «{$catalog->name}» успешно обновлён!");
    }

    // Method for quickly updating balances/prices from a table (In-line edit)
    public function quickUpdate(QuickUpdateCatalogRequest $request, ProductVariant $variant)
    {
        $variant->update($request->validated());

        return redirect()->back()->with('success', 'Данные обновлены');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ProductVariant $catalog)
    {
        $catalog->delete();

        return $this->redirectWithFilters($request, 'admin.catalog.index', "Вариант «{$catalog->name}» успешно удалён!");
    }

    /**
     * Static mapping of sorting options
     */
    private function getSortOptions(): array
    {
        return [
            ['label' => 'Новинки',           'value' => 'newest'],
            ['label' => 'Сначала дешёвые',   'value' => 'price_asc'],
            ['label' => 'Сначала дорогие',   'value' => 'price_desc'],
            ['label' => 'Много на складе',   'value' => 'stock_desc'],
        ];
    }
}
