<?php

namespace App\Http\Controllers;

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
    public function index(Request $request, ValidateDeliveryAction $validateDelivery): Response
    {
        $user = $request->user();

        if ($user) {
            $deliveryDraft = [
                'address' => $user->last_delivery_address ?? $user->defaultDeliveryAddress?->address,
                'lat' => $user->last_delivery_lat ?? $user->defaultDeliveryAddress?->lat,
                'lng' => $user->last_delivery_lng ?? $user->defaultDeliveryAddress?->lng,
                'is_pickup' => false,
                'is_valid' => true,
            ];
        } else {
            $deliveryDraft = session('delivery_draft', [
                'address' => null,
                'lat' => null,
                'lng' => null,
                'is_pickup' => false,
                'is_valid' => false,
            ]);
        }

        $deliveryResult = null;

        // Выполняем расчёт только если выбрана доставка с координатами или самовывоз
        $hasCoords = !empty($deliveryDraft['lat']) && !empty($deliveryDraft['lng']);
        $isPickup = (bool) ($deliveryDraft['is_pickup'] ?? false);

        if ($hasCoords && !$isPickup) {
            try {
                // Инициализация DTO в строгом соответствии с сигнатурой конструктора
                $deliveryDto = new DeliveryDTO(
                    address: $deliveryDraft['address'] ?? null,
                    lat: isset($deliveryDraft['lat']) ? (float) $deliveryDraft['lat'] : null,
                    lng: isset($deliveryDraft['lng']) ? (float) $deliveryDraft['lng'] : null,
                    is_pickup: $isPickup,
                    is_valid: (bool) ($deliveryDraft['is_valid'] ?? true),
                    meta: null
                );

                // Валидация и расчёт через Action
                $deliveryResult = $validateDelivery->handle($deliveryDto);
            } catch (ValidationException $e) {
                // Если адрес вне зоны доставки
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

    public function store(CheckoutRequest $request)
    {
        $checkoutDto = $request->toDTO();
        $deliveryDto = DeliveryDTO::fromRequest($request);

        $order = app(CheckoutService::class)->handle(
            $checkoutDto,
            $deliveryDto
        );

        return redirect()->route('payments.checkout', $order);
    }

    public function success(Order $order)
    {
        // TODO проверить, принадлежит ли заказ текущему пользователю/сессии
        return Inertia::render('Checkout/Success', [
            'order' => $order->load('items.variant.product'),
        ]);
    }
}
