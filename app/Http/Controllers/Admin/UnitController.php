<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Http\Resources\UnitResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::orderBy('position', 'asc')->get();

        return Inertia::render('Admin/Units/Index', [
            'units' => UnitResource::collection($units)
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
            'position' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Unit::create($validated);

        return redirect()->back()->with('success', 'Единица измерения добавлена');
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
            'position' => 'nullable|integer',
        ]);

        $unit->update($validated);

        return redirect()->back()->with('success', 'Обновлено');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        if ($unit->productVariants()->exists()) {
            return redirect()->back()->with('error', 'Нельзя удалить: единица используется в товарах');
        }

        $unit->delete();
        return redirect()->back();
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
