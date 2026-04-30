<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Http\Resources\Admin\AdminUnitResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $units = Unit::query()
            ->when($request->search, function ($query, $search) {
                $search = mb_strtolower($search);
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(short) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$search}%"]);
                });
            })
            ->withCount('productVariants')
            ->orderBy('position', 'asc')
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Units/Index', [
            'units' => AdminUnitResource::collection($units),
            'filters' => $request->only(['search']),
            'seo' => $this->seo('Панель управления: Номенклатура', robots: 'noindex, nofollow')
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
            'name' => 'required|string|max:255',
            'short' => 'required|string|max:50',
            'slug' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
        ]);

        $unitName = $validated['name'];

        $slugSource = $validated['slug'] ?: $validated['short'];
        $slug = Str::customSlug($slugSource);

        $count = Unit::where('slug', 'LIKE', "{$slug}%")->count();
        $validated['slug'] = $count ? "{$slug}-{$count}" : $slug;

        Unit::create($validated);

        return redirect()->back()->with('success', "Единица «{$unitName}» измерения  создана");
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
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short' => 'required|string|max:50',
            'slug' => 'nullable|string|unique:units,slug,' . $unit->id,
            'position' => 'nullable|integer|min:0',
        ]);
        
        $validated['slug'] = Str::customSlug($validated['slug'] ?? $validated['short']);

        $unit->update($validated);

        return redirect()->back()->with('success', "Данные «{$unit->name}» обновлены");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        if ($unit->productVariants()->exists()) {
            return redirect()->back()->with('error', "Нельзя удалить  «{$unit->name}»: единица используется в товарах");
        }

        $unit->delete();
        return redirect()->back()->with('success', "Единица измерения «{$unit->name}» удалена");
    }

    public function reorder(Request $request)
    {
        $ids = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:units,id'
        ])['ids'];

        foreach ($ids as $index => $id) {
            Unit::where('id', $id)->update(['position' => $index]);
        }

        return back()->with('success', 'Порядок обновлен');
    }
}
