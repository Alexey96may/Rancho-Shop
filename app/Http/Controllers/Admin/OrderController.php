<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    use HandlesSmartPagination;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);

        $orders = Order::query()
            ->with(['user', 'promoCode', 'items.product.media'])
            ->filter($filters)
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => $filters,
            ...Order::getStats(),
            'seo' => $this->seo('Панель управления: Заказы', 'Просмотр заказов',  robots: 'noindex, nofollow')
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $order->load(['user', 'promoCode', 'items.product.media']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => new OrderResource($order),
            'seo' => $this->seo("Заказ #{$order->id}", "Просмотр деталей заказа", robots: 'noindex, nofollow'),
            'backUrl' => $request->query('back') 
                ? route('admin.orders.index') . $request->query('back')
                : route('admin.orders.index'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $dto = $request->toDto();
        $order->update($dto->toArray());

        return back()->with('success', "Данные заказа #{$order->id} обновлены.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Order $order)
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'У вас недостаточно прав для удаления заказа.');
        }

        $order->delete();

        return $this->redirectWithFilters($request, 'admin.orders.index', "Заказ #{$order->id} успешно удалён из базы!");
    }
}
