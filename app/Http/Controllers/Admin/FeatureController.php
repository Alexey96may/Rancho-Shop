<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminLandingBlockResource;
use App\Http\Requests\Admin\UpdateLandingBlockRequest;
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
        $filters = $request->only(['search']);

        $blocks = LandingBlock::query()
            ->filter($filters)
            ->orderBy('is_visible', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Admin/Features/Index', [
            'blocks' => AdminLandingBlockResource::collection($blocks),
            'filters' => $filters,
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
    public function update(UpdateLandingBlockRequest $request, LandingBlock $feature)
    {
        $dto = $request->toDto();
        
        $feature->update($dto->toArray());

        return redirect()->route('admin.features.index')->with('success', "Блок {$feature->key->label()} обновлён!");
    }

    /**
     * Toggle block visibility on the landing page.
     */
    public function toggle(LandingBlock $feature)
    {
        $feature->update([
            'is_visible' => !$feature->is_visible
        ]);

        $status = $feature->is_visible ? "отображается" : "скрыт";

        return redirect()->back()->with('success', "Блок «{$feature->key->label()}» теперь {$status}");
    }
}
