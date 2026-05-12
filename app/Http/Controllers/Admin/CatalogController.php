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
            // Здесь можно добавить фильтрацию через scopeFilter, как мы делали для продуктов
            ->when($request->search, function($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('position', 'asc')
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Catalog/Index', [
            'variants' => AdminProductVariantResource::collection($variants),
            'filters' => $request->all(['search', 'stock']),
            'seo' => $this->seo('Панель управления: Прайс-лист', 'Просмотр вариантов товаров',  robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Catalog/Create', [
            'products' => Product::select(['id', 'name'])->orderBy('name')->get(),
            'units'    => Unit::select(['id', 'short', 'name'])->orderBy('name')->get(),
            'seo' => $this->seo('Создание нового варианта товара', robots: 'noindex, nofollow'),
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

        ProductVariant::create($validated);

        return redirect()->route('admin.catalog.index')
            ->with('success', "Вариант {$validated['name']} успешно создан");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductVariant $variant)
    {
        $variant->load(['product.media', 'unit']);

        return Inertia::render('Admin/Catalog/Edit', [
            'products' => Product::select(['id', 'name'])->orderBy('name')->get(),
            'variant'  => new AdminProductVariantResource($variant),
            'units'    => Unit::select(['id', 'short', 'name'])->orderBy('name')->get(),
            'seo' => $this->seo('Редактирование варианта: ' . $variant->name, robots: 'noindex, nofollow'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'unit_id'    => 'required|exists:units,id',
            'name'       => 'required|string|max:255',
            'price'      => 'required|integer|min:0',
            'old_price'  => 'nullable|integer|min:0',
            'stock'      => 'required|integer|min:0',
            'is_default' => 'boolean',
            'position'   => 'nullable|integer',
            'attributes' => 'nullable|array',
        ]);

        $variant->update($validated);

        return redirect()->route('admin.catalog.index')
            ->with('success', 'Вариант'  . $variant->name . ' обновлен');
    }

    // Method for quickly updating balances/prices from a table (In-line edit)
    public function quickUpdate(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'price' => 'sometimes|integer|min:0',
            'stock' => 'sometimes|integer|min:0',
            'is_default' => 'sometimes|boolean'
        ]);
        
        // Allow updating only the price or balance
        $variant->update($validated);

        return redirect()->back()->with('success', 'Данные обновлены');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductVariant $variant)
    {
        $variant->delete();

        return back()->with('success', 'Вариант'  . $variant->name . ' удален');
    }
}
