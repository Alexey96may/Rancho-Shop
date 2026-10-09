<?php

namespace App\Http\Controllers\Profile;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $request->user()->orders()
            ->with(['items.product.media'])
            ->latest()
            ->paginate(setting('orders_per_page', 12))
            ->withQueryString();

        return Inertia::render('Profile/Orders', [
            'orders' => OrderResource::collection($orders),
            'seo' => $this->seo('Мои Заказы', robots: 'noindex, nofollow'),
        ]);
    }

    public function destroy(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if ($order->status !== OrderStatus::NEW) {
            return back()->with('error', 'Этот заказ уже нельзя отменить.');
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Заказ успешно отменен.');
    }
}
