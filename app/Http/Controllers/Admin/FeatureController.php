<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminLandingBlockResource;
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
                $search = mb_strtolower($search, 'UTF-8');

                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(key) LIKE ?', ["%{$search}%"]);
                });
            })
            ->orderBy('is_visible', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Admin/Features/Index', [
            'blocks' => AdminLandingBlockResource::collection($blocks),
            'filters' => $request->only(['search']),
            'seo' => $this->seo('Панель управления: Блоки', robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LandingBlock $feature)
    {
        return Inertia::render('Admin/Features/Edit', [
            'block' => new AdminLandingBlockResource($feature),
            'seo' => $this->seo("Редактирование: {$feature->key->label()}", robots: 'noindex, nofollow'),
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
    public function toggle(LandingBlock $feature)
    {
        $feature->update(['is_visible' => !$feature->is_visible]);
        $status = $feature->is_visible ? "отображается" : "скрыт";

        return redirect()->back()->with('success', "Блок «{$feature->key->label()}» теперь {$status}");
    }
}
