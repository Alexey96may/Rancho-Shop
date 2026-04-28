<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PromoCodeType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminPromoCodeResource;
use App\Models\PromoCode;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromocodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $promoCodes = PromoCode::query()
            ->when($request->search, function ($query, $search) {
                $query->where('code', 'LIKE', "%" . strtoupper($search) . "%");
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('is_active', 'desc')
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        $typeOptions = collect(PromoCodeType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ]);

        return Inertia::render('Admin/PromoCodes/Index', [
            'promoCodes' => AdminPromoCodeResource::collection($promoCodes),
            'filters'    => $request->only(['search', 'type']),
            'typeOptions' => $typeOptions,
            'seo' => $this->seo('Панель управления: Промокоды', robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'             => 'required|string|unique:promo_codes,code|max:50',
            'type'             => ['required', new Enum(PromoCodeType::class)],
            'value'            => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount'     => 'nullable|integer|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'expires_at'       => 'nullable|date|after:today',
            'is_active'        => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        PromoCode::create($validated);

        return redirect()->route('admin.promocodes.index')
            ->with('success', "Промокод {$request->code} создан");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PromoCode $promoCode)
    {
        $validated = $request->validate([
            'code'             => 'required|string|max:50|unique:promo_codes,code,' . $promoCode->id,
            'type'             => ['required', new Enum(PromoCodeType::class)],
            'value'            => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount'     => 'nullable|integer|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'expires_at'       => 'nullable|date|after:today',
            'is_active'        => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $promoCode->update($validated);

        if ($request->has('create_another')) {
            return redirect()->back()->with('success', 'Создано. Можете добавить следующий.');
        }

        return redirect()->route('admin.promocodes.index')
            ->with('success', "Промокод {$request->code} обновлен");
    }

    public function create()
    {
        $typeOptions = collect(PromoCodeType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ]);

        return Inertia::render('Admin/PromoCodes/Create', [
            'typeOptions' => $typeOptions,
            'seo' => $this->seo('Новый промокод', robots: 'noindex, nofollow'),
        ]);
    }

    public function edit(PromoCode $promocode)
    {
        $typeOptions = collect(PromoCodeType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ]);

        return Inertia::render('Admin/PromoCodes/Edit', [
            'promo' => new AdminPromoCodeResource($promocode),
            'typeOptions' => $typeOptions,
            'seo' => $this->seo('Редактирование промокода ' . $promocode->code, robots: 'noindex, nofollow'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        return redirect()->route('admin.promocodes.index')
            ->with('success', 'Промокод удален');
    }

    /**
     * A useful method for quickly changing your status (toggle)
     */
    public function toggle(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => !$promoCode->is_active]);
        return redirect()->back();
    }
}
