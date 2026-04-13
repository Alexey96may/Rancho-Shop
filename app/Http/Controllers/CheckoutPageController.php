<?php

namespace App\Http\Controllers;

use Inertia\Response;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutPageController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Checkout/Index', [
            'delivery' => $request->attributes->get('delivery'),
        ]);
    }

    public function store(\App\Http\Requests\CheckoutRequest $request)
    {
        $delivery = $request->attributes->get('delivery_draft');

        if (!$delivery || empty($delivery['address'])) {
            return back()->withErrors([
                'delivery' => 'Не выбран адрес доставки',
            ]);
        }

        $order = app(\App\Services\CheckoutService::class)
            ->handle(
                $request->toDTO(),
                $delivery
            );

        return redirect()
            ->route('checkout.success', $order->id)
            ->with('success', 'Заказ успешно создан!');
    }

}
