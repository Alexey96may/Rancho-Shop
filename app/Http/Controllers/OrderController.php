<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Inertia\Inertia;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->latest()
            ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('User/Orders/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only(['status']), 
        ]);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['items']);

        return Inertia::render('User/Orders/Show', [
            'order' => new OrderResource($order),
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $items = $request->validated()['items'];

        $total = 0;

        foreach ($items as $item) {

            $variant = ProductVariant::with('product')
                ->findOrFail($item['variant_id']);

            // ⚠️ всегда берём актуальную цену из БД
            $price = $variant->price;

            // проверка stock
            if ($variant->stock < $item['quantity']) {
                return back()->withErrors([
                    'stock' => "Недостаточно товара: {$variant->name}"
                ]);
            }

            $total += $price * $item['quantity'];
        }

        $order = Order::create([
            'total_price' => $total,
            'status' => 'new',
        ]);
        
        return back()->with('success', 'Заказ принят!');
    }
}
