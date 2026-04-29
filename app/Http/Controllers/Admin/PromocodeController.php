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
            'expires_at'       => 'nullable|date',
            'is_active'        => 'boolean',
            'create_another'   => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        PromoCode::create($validated);

        if ($request->boolean('create_another')) {
            return redirect()->back()->with('success', "Промокод {$validated['code']} создан. Можете добавить следующий.");
        }

        return redirect()->route('admin.promocodes.index', ['page' => $request->input('return_page', 1)])
            ->with('success', "Промокод {$request->code} создан");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PromoCode $promocode)
    {
        $validated = $request->validate([
            'code'             => "required|string|max:50|unique:promo_codes,code,{$promocode->id}",
            'type'             => ['required', new Enum(PromoCodeType::class)],
            'value'            => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount'     => 'nullable|integer|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'expires_at'       => 'nullable|date',
            'is_active'        => 'boolean',
            'return_page'      => 'nullable',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $promocode->update($validated);

        return redirect()->route('admin.promocodes.index', ['page' => $request->input('return_page', 1)])
            ->with('success', "Промокод {$promocode->code} обновлен");
    }

    public function create(Request $request)
    {
        $typeOptions = collect(PromoCodeType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ]);

        return Inertia::render('Admin/PromoCodes/Create', [
            'typeOptions' => $typeOptions,
            'filters' => [
                'page' => $request->query('page', 1),
            ],
            'seo' => $this->seo('Новый промокод', robots: 'noindex, nofollow'),
        ]);
    }

    public function edit(PromoCode $promocode, Request $request)
    {
        $typeOptions = collect(PromoCodeType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ]);

        return Inertia::render('Admin/PromoCodes/Edit', [
            'promo' => new AdminPromoCodeResource($promocode),
            'typeOptions' => $typeOptions,
            'filters' => [
                'page' => $request->query('page', 1),
            ],
            'seo' => $this->seo('Редактирование промокода ' . $promocode->code, robots: 'noindex, nofollow'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PromoCode $promocode)
    {
        $promocode->delete();

        return redirect()->route('admin.promocodes.index')
            ->with('success', 'Промокод ' . $promocode->code . ' удален');
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
