<?php

namespace App\Http\Controllers;

use Inertia\Response;

use Inertia\Inertia;

class CheckoutPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Checkout/Index', [
            // позже:
            // cart snapshot (если нужно)
            // user data
        ]);
    }

    public function store(\App\Http\Requests\CheckoutRequest $request)
    {
        $order = app(\App\Services\CheckoutService::class)
            ->handle($request->toDTO());

        return redirect()
            ->route('checkout.success', $order->id)
            ->with('success', 'Заказ успешно создан!');
    }

}
