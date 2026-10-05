<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
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
            ->with(['items.product'])
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return Inertia::render('Profile/Orders', [
            'orders' => $orders,
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if (!in_array($order->status, ['new', 'confirmed'])) {
            return back()->with('error', 'Этот заказ уже нельзя отменить.');
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Заказ успешно отменен.');
    }
}