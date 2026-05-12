<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Unit;
use App\Models\ProductVariant;
use App\Http\Resources\Admin\AdminProductResource;
use App\Http\Resources\Admin\AdminProductVariantResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $variants = ProductVariant::query()
            ->with(['product.media', 'unit'])
            ->when($request->search, function($q, $search) {
                $searchTerm = mb_strtolower($search, 'UTF-8');

                $q->where(fn($query) => 
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                        ->orWhere('id', 'like', "%{$searchTerm}%")
                        ->orWhereHas('product', fn($pq) => 
                            $pq->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                        )
                );
            })
            ->when($request->product_id, fn($q, $id) => $q->where('product_id', $id))
            ->when($request->unit_id, fn($q, $id) => $q->where('unit_id', $id))
            ->when($request->sort, function($q, $sort) {
                switch ($sort) {
                    case 'price_asc': $q->orderBy('price', 'asc'); break;
                    case 'price_desc': $q->orderBy('price', 'desc'); break;
                    case 'stock_desc': $q->orderBy('stock', 'desc'); break;
                    case 'newest': $q->latest(); break;
                    default: $q->orderBy('position', 'asc');
                }
            }, fn($q) =>  $q->orderBy('position', 'asc'))
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Catalog/Index', [
            'variants' => AdminProductVariantResource::collection($variants),
            'products' => Product::select(['id', 'name'])->orderBy('name')->get(),
            'units' => \App\Models\Unit::select(['id', 'name'])->get(),
            'filters' => $request->all(['search', 'product_id', 'unit_id', 'sort']),
            'sortOptions' => [
                ['label' => 'По умолчанию', 'value' => 'default'],
                ['label' => 'Новинки', 'value' => 'newest'],
                ['label' => 'Сначала дешевые', 'value' => 'price_asc'],
                ['label' => 'Сначала дорогие', 'value' => 'price_desc'],
                ['label' => 'Много на складе', 'value' => 'stock_desc'],
            ],
            'seo' => $this->seo('Панель управления: Прайс-лист', 'Просмотр вариантов товаров',  robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $page = $request->input('page', 1);

        return Inertia::render('Admin/Catalog/Form', [
            'products' => Product::select(['id', 'name'])->orderBy('name')->get(),
            'units'    => Unit::select(['id', 'short', 'name'])->orderBy('name')->get(),
            'seo' => $this->seo('Создание нового варианта товара', robots: 'noindex, nofollow'),
            'currentPage' => $page,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_default' => 'boolean',
            'position' => 'nullable|integer',
            'attributes' => 'nullable|array',
        ]);

        $variant = ProductVariant::create($validated);
        $page = $request->input('page', 1);

        if ($request->boolean('create_another')) {
            return redirect()->back()
                ->with('success', "Вариант {$variant->name} создан. Можете добавить следующий.");
        }

        return redirect()->route('admin.catalog.index', ['page' => $page])
            ->with('success', "Вариант {$variant->name} успешно создан");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductVariant $catalog, Request $request)
    {
        $catalog->load(['product.media', 'unit']);

        $page = $request->input('page', 1);

        return Inertia::render('Admin/Catalog/Form', [
            'products' => Product::select(['id', 'name'])->orderBy('name')->get(),
            'variant'  => new AdminProductVariantResource($catalog),
            'units'    => Unit::select(['id', 'short', 'name'])->orderBy('name')->get(),
            'seo' => $this->seo('Редактирование варианта: ' . $catalog->name, robots: 'noindex, nofollow'),

            'isEdit' => true,
            'currentPage' => $page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductVariant $catalog)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'unit_id'    => 'required|exists:units,id',
            'name'       => 'required|string|max:255',
            'price'      => 'required|integer|min:0',
            'old_price'  => 'nullable|integer|min:0',
            'stock'      => 'required|integer|min:0',
            'is_default' => ['boolean',
                function ($attribute, $value, $fail) use ($catalog) {
                    if ($value === false && $catalog->is_default && $catalog->product->variants()->count() === 1) {
                        $fail('Нельзя снять флаг "По умолчанию", если это единственный вариант товара.');
                    }
                },
            ],
            'position'   => 'nullable|integer',
            'attributes' => 'nullable|array',
        ]);

        $catalog->update($validated);

        $page = $request->input('page', 1);

        return redirect()->route('admin.catalog.index', ['page' => $page])
            ->with('success', 'Вариант'  . $catalog->name . ' обновлен');
    }

    // Method for quickly updating balances/prices from a table (In-line edit)
    public function quickUpdate(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'price' => 'sometimes|integer|min:0',
            'stock' => 'sometimes|integer|min:0',
            'is_default' => ['sometimes', 'boolean',
                function ($attribute, $value, $fail) use ($variant) {
                    if ($value === false && $variant->is_default && $variant->product->variants()->count() === 1) {
                        $fail('Нельзя снять флаг "По умолчанию", если это единственный вариант товара.');
                    }
                },
            ],
        ]);
        
        $variant->update($validated);

        return redirect()->back()->with('success', 'Данные обновлены');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductVariant $catalog)
    {
        $catalog->delete();

        return back()->with('success', 'Вариант «'  . $catalog->name . '» удален');
    }
}
