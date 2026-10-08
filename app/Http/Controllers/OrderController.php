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


}
