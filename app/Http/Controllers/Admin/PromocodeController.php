<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoCodeResource;
use App\Models\PromoCode;
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
                $query->where('code', 'ilike', "%{$search}%");
            })
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/PromoCodes/Index', [
            'promoCodes' => PromoCodeResource::collection($promoCodes),
            'filters'    => $request->only(['search']),
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
            'code'             => 'required|string|unique:promo_codes,code|max:50',
            'type'             => 'required|in:fixed,percent',
            'value'            => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount'     => 'nullable|integer|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'expires_at'       => 'nullable|date|after:today',
            'is_active'        => 'boolean',
        ]);

        // Приводим код к верхнему регистру
        $validated['code'] = strtoupper($validated['code']);

        PromoCode::create($validated);

        return redirect()->route('admin.promo-codes.index')
            ->with('success', 'Промокод успешно создан');
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
    public function update(Request $request, PromoCode $promoCode)
    {
        $validated = $request->validate([
            'code'             => 'required|string|max:50|unique:promo_codes,code,' . $promoCode->id,
            'type'             => 'required|in:fixed,percent',
            'value'            => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount'     => 'nullable|integer|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'expires_at'       => 'nullable|date',
            'is_active'        => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $promoCode->update($validated);

        return redirect()->back()
            ->with('success', 'Промокод обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        return redirect()->route('admin.promo-codes.index')
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
