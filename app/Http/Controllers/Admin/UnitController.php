<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Http\Resources\Admin\AdminUnitResource;
use App\Http\Requests\Admin\{UnitSaveRequest, UnitReorderRequest};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);

        $units = Unit::query()
            ->filter($filters)
            ->withCount('productVariants')
            ->orderBy('position', 'asc')
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Units/Index', [
            'units' => AdminUnitResource::collection($units),
            'filters' => $filters,
            'seo' => $this->seo('Панель управления: Номенклатура', robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnitSaveRequest $request)
    {
        $dto = $request->toDto();
        
        Unit::create($dto->toArray());

        return redirect()->back()->with('success', "Единица «{$dto->name}» измерения  создана");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnitSaveRequest $request, Unit $unit)
    {
        $dto = $request->toDto();
        
        $unit->update($dto->toArray());

        return redirect()->back()->with('success', "Данные «{$unit->name}» обновлены");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        if ($unit->productVariants()->exists()) {
            return redirect()->back()->with('error', "Нельзя удалить «{$unit->name}»: единица используется в товарах");
        }

        $unit->delete();
        return redirect()->back()->with('success', "Единица измерения «{$unit->name}» удалена");
    }

    /**
     * Change order of a Unit.
     */
    public function reorder(UnitReorderRequest $request)
    {
        $ids = $request->validated('ids');

        DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                Unit::where('id', $id)->update(['position' => $index]);
            }
        });

        return back()->with('success', 'Порядок обновлен');
    }
}
