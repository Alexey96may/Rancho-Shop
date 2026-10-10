<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PromoCodeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromoCodeSaveRequest;
use App\Http\Resources\Admin\AdminPromoCodeResource;
use App\Models\PromoCode;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromocodeController extends Controller
{
    use HandlesSmartPagination;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type', 'status', 'sort']);

        $promoCodes = PromoCode::query()
            ->filter(collect($filters)->only(['search', 'type', 'status'])->all())
            ->applySorting($filters['sort'] ?? 'latest')
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/PromoCodes/Index', [
            'promoCodes' => AdminPromoCodeResource::collection($promoCodes),
            'filters' => $filters,
            'typeOptions' => $this->getTypeOptions(),
            'statusOptions' => $this->getStatusOptions(),
            'sortOptions' => $this->getSortOptions(),
            'seo' => $this->seo('Панель управления: Промокоды', robots: 'noindex, nofollow'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PromoCodeSaveRequest $request)
    {
        $dto = $request->toDto();
        PromoCode::create($dto->toArray());

        if ($request->boolean('create_another')) {
            return redirect()->back()->with('success', 'Промокод создан. Можете добавить следующий.');
        }

        return $this->redirectWithFilters($request, 'admin.promocodes.index', 'Промокод создан!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PromoCodeSaveRequest $request, PromoCode $promocode)
    {
        $dto = $request->toDto();
        $promocode->update($dto->toArray());

        return $this->redirectWithFilters($request, 'admin.promocodes.index', "Промокод «{$promocode->code}» успешно обновлён!");
    }

    public function create(Request $request)
    {
        $typeOptions = collect(PromoCodeType::cases())->map(fn ($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ]);

        return Inertia::render('Admin/PromoCodes/Create', [
            'typeOptions' => $typeOptions,
            'backUrl' => $request->query('back')
                    ? route('admin.promocodes.index') . $request->query('back')
                    : route('admin.promocodes.index'),
            'seo' => $this->seo('Новый промокод', robots: 'noindex, nofollow'),
        ]);
    }

    public function edit(PromoCode $promocode, Request $request)
    {
        return Inertia::render('Admin/PromoCodes/Edit', [
            'promo' => new AdminPromoCodeResource($promocode),
            'typeOptions' => $this->getTypeOptions(),
            'backUrl' => $request->query('back')
                    ? route('admin.promocodes.index') . $request->query('back')
                    : route('admin.promocodes.index'),
            'seo' => $this->seo('Редактирование промокода ' . $promocode->code, robots: 'noindex, nofollow'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, PromoCode $promocode)
    {
        $promocode->delete();

        return $this->redirectWithFilters($request, 'admin.promocodes.index', "Промокод «{$promocode->code}» успешно удалён!");
    }

    /**
     * A useful method for quickly changing your status (toggle)
     */
    public function toggle(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => !$promoCode->is_active]);

        return redirect()->back();
    }

    // ==========================================
    // Helpers
    // ==========================================

    private function getTypeOptions(): array
    {
        return collect(PromoCodeType::cases())->map(fn ($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ])->toArray();
    }

    private function getStatusOptions(): array
    {
        return [
            ['value' => 'new', 'label' => 'Новинки (7 дней)'],
            ['value' => 'expiring', 'label' => 'Истекают скоро'],
        ];
    }

    private function getSortOptions(): array
    {
        return [
            ['value' => 'latest', 'label' => 'Сначала новые'],
            ['value' => 'expires_at', 'label' => 'По дате истечения'],
        ];
    }
}
