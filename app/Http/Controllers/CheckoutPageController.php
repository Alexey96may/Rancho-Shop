<?php

namespace App\Http\Controllers;

use Inertia\Response;

use App\Http\Requests\CheckoutRequest;
use Illuminate\Http\Request;
use App\Models\Order;
use Inertia\Inertia;
use App\Services\CheckoutService;
use App\DTO\DeliveryDTO;

class CheckoutPageController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user) {
            $deliveryDraft = [
                'address'  => $user->last_delivery_address ?? $user->defaultDeliveryAddress?->address,
                'lat'      => $user->last_delivery_lat ?? $user->defaultDeliveryAddress?->lat,
                'lng'      => $user->last_delivery_lng ?? $user->defaultDeliveryAddress?->lng,
                'is_valid' => true,
            ];
        } else {
            $deliveryDraft = session('delivery_draft', []);
        }

        return Inertia::render('Checkout/Index', [
            'delivery_draft' => $deliveryDraft,
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $checkoutDto = $request->toDTO();
        $deliveryDto = DeliveryDTO::fromRequest($request);

        $order = app(CheckoutService::class)->handle(
            $checkoutDto,
            $deliveryDto
        );

        return redirect()
            ->route('checkout.success', $order->id)
            ->with('success', 'Заказ успешно создан!');
    }

    public function success(Order $order)
    {
        // TODO проверить, принадлежит ли заказ текущему пользователю/сессии

        return Inertia::render('Checkout/Success', [
            'order' => $order->load('items.variant.product'),
        ]);
    }

}
