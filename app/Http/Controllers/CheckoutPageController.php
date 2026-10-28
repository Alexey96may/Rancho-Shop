<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\GetDeliveryDraftAction;
use App\Actions\Checkout\ValidateDeliveryAction;
use App\DTO\DeliveryDTO;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutPageController extends Controller
{
    public function index(
        Request $request,
        GetDeliveryDraftAction $getDraft,
        ValidateDeliveryAction $validateDelivery,
    ): Response {
        $deliveryDraft = $getDraft->handle($request->user(), $request);

        $deliveryResult = null;
        $hasCoords = !empty($deliveryDraft['lat']) && !empty($deliveryDraft['lng']);
        $isPickup = (bool) ($deliveryDraft['is_pickup'] ?? false);

        if ($hasCoords && !$isPickup) {
            try {
                $deliveryDto = new DeliveryDTO(
                    address: $deliveryDraft['address'] ?? null,
                    lat: (float) $deliveryDraft['lat'],
                    lng: (float) $deliveryDraft['lng'],
                    is_pickup: $isPickup,
                    is_valid: (bool) ($deliveryDraft['is_valid'] ?? true),
                    meta: null,
                );

                $deliveryResult = $validateDelivery->handle($deliveryDto);
            } catch (ValidationException $e) {
                $deliveryResult = [
                    'is_valid' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return Inertia::render('Checkout/Index', [
            'delivery_draft' => $deliveryDraft,
            'delivery_result' => $deliveryResult,
            'seo' => $this->seo('Оплата покупки', 'Оплатите покупки в нашем магазине', robots: 'noindex, nofollow'),
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkoutService)
    {
        $checkoutDto = $request->toDTO();
        $deliveryDto = DeliveryDTO::fromRequest($request);

        $result = $checkoutService->handle($checkoutDto, $deliveryDto);

        $order = $result['order'];
        $magicLink = $result['magicLink'];

        if ($magicLink) {
            session(["magic_link.{$order->id}" => $magicLink]);
        }

        // Промокод применён к заказу — очищаем сессию
        session()->forget('promo_code_id');

        $paymentUrl = route('payments.checkout', [
            'order' => $order,
            'token' => $order->payment_token,
        ]);

        return redirect($paymentUrl);
    }

    public function success(Order $order)
    {
        $magicLink = session()->pull("magic_link.{$order->id}");

        return Inertia::render('Checkout/Success', [
            'order' => $order->load('items.variant.product'),
            'magicLink' => $magicLink,
        ]);
    }
}
