<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Units\CreateUnitAction;
use App\Actions\Admin\Units\DeleteUnitAction;
use App\Actions\Admin\Units\ReorderUnitAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UnitReorderRequest;
use App\Http\Requests\Admin\UnitSaveRequest;
use App\Http\Resources\Admin\AdminUnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RuntimeException;

class UnitController extends Controller
{
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
            'seo' => $this->seo('Панель управления: Номенклатура', robots: 'noindex, nofollow'),
        ]);
    }

    public function store(UnitSaveRequest $request, CreateUnitAction $action)
    {
        $unit = $action->handle($request->toDto()->toArray());

        return redirect()->back()->with('success', "Единица «{$unit->name}» измерения создана");
    }

    public function update(UnitSaveRequest $request, Unit $unit)
    {
        $unit->update($request->toDto()->toArray());

        return redirect()->back()->with('success', "Данные «{$unit->name}» обновлены");
    }

    public function destroy(Unit $unit, DeleteUnitAction $action)
    {
        $name = $unit->name;

        try {
            $action->handle($unit);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', "Единица измерения «{$name}» удалена");
    }

    public function reorder(UnitReorderRequest $request, ReorderUnitAction $action)
    {
        $action->handle($request->validated('ids'));

        return back()->with('success', 'Порядок обновлён');
    }
}
