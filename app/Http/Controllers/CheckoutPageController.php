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
        ]);
    }

    public function store(\App\Http\Requests\CheckoutRequest $request)
    {
        $deliveryDraft  = $request->attributes->get('delivery_draft');

        if (!$deliveryDraft) {
            return back()->withErrors([
                'delivery' => 'Не выбран способ доставки',
            ]);
        }

        $delivery = new \App\DTO\DeliveryDTO(
            address: $deliveryDraft['address'] ?? null,
            lat: $deliveryDraft['lat'] ?? null,
            lng: $deliveryDraft['lng'] ?? null,
            is_pickup: $deliveryDraft['is_pickup'] ?? false,
            is_valid: $deliveryDraft['is_valid'] ?? false,
            meta: $deliveryDraft['delivery_meta'] ?? null,
        );

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
