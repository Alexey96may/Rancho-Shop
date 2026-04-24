<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Http\Resources\ProductVariantResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $variants = ProductVariant::with(['product.media', 'unit'])
            ->orderBy('position', 'asc')
            ->paginate(15);

        return Inertia::render('Admin/Catalog/Index', [
            'variants' => ProductVariantResource::collection($variants),
            'filters' => request()->all(['search', 'stock']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Catalog/Create', [
        
            'products' => \App\Models\Product::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get(),
            'units' => \App\Models\Unit::query()
                ->select(['id', 'short', 'name'])
                ->orderBy('name')
                ->get(),
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

        $validated['price'] = $validated['price'] * 100;
        if ($validated['old_price']) {
            $validated['old_price'] = $validated['old_price'] * 100;
        }

        ProductVariant::create($validated);

        return redirect()->route('admin.catalog.index')
            ->with('success', 'Вариант товара успешно создан');
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
    public function edit($id)
    {
        $variant = ProductVariant::with(['product', 'unit'])->findOrFail($id);

        return Inertia::render('Admin/Catalog/Edit', [
            'variant' => new ProductVariantResource($variant)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $variant = ProductVariant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer',
            'stock' => 'required|integer|min:0',
            'is_default' => 'boolean',
        ]);

        $variant->update($validated);

        return redirect()->route('admin.catalog.index')
            ->with('success', 'Товар обновлен');
    }

    // Method for quickly updating balances/prices from a table (In-line edit)
    public function quickUpdate(Request $request, $id)
    {
        $variant = ProductVariant::findOrFail($id);
        
        // Allow updating only the price or balance
        $variant->update($request->only(['price', 'stock', 'is_visible']));

        return redirect()->back()->with('success', 'Данные обновлены');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
