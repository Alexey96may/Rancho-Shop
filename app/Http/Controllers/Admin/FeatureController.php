<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LandingBlockResource;
use App\Models\LandingBlock;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $blocks = LandingBlock::query()
            ->when($request->search, function ($query, $search) {
                // Поиск по заголовку или ключу
                $query->where('title', 'ilike', "%{$search}%")
                      ->orWhere('key', 'ilike', "%{$search}%");
            })
            ->orderBy('is_visible', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Admin/Features/Index', [
            'blocks' => LandingBlockResource::collection($blocks),
            'filters' => $request->only(['search']),
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
        //
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
    public function edit(LandingBlock $feature)
    {
        return Inertia::render('Admin/Features/Edit', [
            'block' => new LandingBlockResource($feature)
        ]);
    }

    /**
    * Updating a block.
    * We don't create new blocks through the UI (they're from the seeder),
    * but we do provide the ability to edit existing ones.
    */
    public function update(Request $request, LandingBlock $feature)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:500',
            'subtitle'   => 'nullable|string|max:500',
            'is_visible' => 'boolean',
            'content'    => 'required|array',
            'content.*.title' => 'nullable|string|max:255',
            'content.*.desc'  => 'required|string',
            'content.*.icon'  => 'nullable|string',
            'content.*.step'  => 'nullable|integer',
        ]);

        $feature->update($validated);

        return redirect()->route('admin.features.index')->with('success', "Блок обновлен");
    }

    /**
     * Toggle block visibility on the landing page.
     */
    public function toggle(int $id)
    {
        $feature = LandingBlock::findOrFail($id);
        $feature->is_visible = !$feature->is_visible;
        $feature->save(); 
        return redirect()->back()->with('success', $feature->is_visible ? "Блок видно на сайте" : "Блок скрыт");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
