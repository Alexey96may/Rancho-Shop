<?php

namespace App\Http\Controllers;

use Inertia\Response;

use App\Http\Requests\CheckoutRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\CheckoutService;
use App\DTO\DeliveryDTO;

class CheckoutPageController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Checkout/Index', [
        ]);
    }

    public function store(CheckoutRequest $request, DeliveryDTO $delivery)
    {
        $order = app(CheckoutService::class)->handle(
            $request->toDTO(),
            $delivery
        );

        return redirect()
            ->route('checkout.success', $order->id)
            ->with('success', 'Заказ успешно создан!');
    }

}
