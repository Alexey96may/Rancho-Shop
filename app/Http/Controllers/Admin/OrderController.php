<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $totalCompleted = Order::where('status', 'completed')->sum('total_price');
        $totalPending = Order::whereIn('status', ['confirmed', 'delivering'])
        ->sum('total_price');

        $orders = Order::query()
            ->with(['user', 'promoCode', 'items.product.media'])
            ->when($request->search, function ($query, $search) {
                $search = mb_strtolower($search, 'UTF-8');
                
                $query->where(function($q) use ($search) {
                    $q->whereRaw('LOWER(customer_name) LIKE ?', ["%{$search}%"])
                    ->orWhere('id', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only(['search', 'status']),
            'total_count' => Order::count(),
            'total_completed_revenue' => (int) $totalCompleted,
            'total_pending_revenue' => (int) $totalPending,
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
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,confirmed,delivering,completed,cancelled',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        if ($request->filled('admin_note') && $request->admin_note !== $order->admin_note) {
            if (!$request->user()->isAdmin()) {
                abort(403, 'Только администратор может редактировать заметки.');
            }
        }

        $order->update($validated);

        return back()->with('success', "Данные заказа #{$order->id} обновлен.");
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

        return redirect()->route('admin.orders.index')->with('warning', "Заказ #{$order->id} удалён из базы.");
    }
}
