<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\DTO\DeliveryDTO;

class ResolveDeliveryDraft
{
    public function handle(Request $request, Closure $next): Response
    {
        $draft = null;

        /**
         * 👤 AUTH USER
         */
        if ($request->user()) {
            $address = $request->user()
                ->deliveryAddresses()
                ->latest()
                ->first();

            if ($address) {
                $draft = [
                    'address' => $address->address,
                    'lat' => $address->lat,
                    'lng' => $address->lng,
                    'is_pickup' => false,
                    'is_valid' => true,
                    'meta' => null,
                ];
            }
        }

        /**
         * 👤 GUEST (session)
         */
        if (!$draft) {
            $sessionDraft = session('delivery_draft');

            if ($sessionDraft) {
                $draft = [
                    'address' => $sessionDraft['address'] ?? null,
                    'lat' => $sessionDraft['lat'] ?? null,
                    'lng' => $sessionDraft['lng'] ?? null,
                    'is_pickup' => $sessionDraft['is_pickup'] ?? false,
                    'is_valid' => $sessionDraft['is_valid'] ?? false,
                    'meta' => $sessionDraft['delivery_meta'] ?? null,
                ];
            }
        }

        /**
         * 🟢 ЕСЛИ НЕТ → САМОВЫВОЗ
         */
        if (!$draft) {
            $delivery = new DeliveryDTO(
                address: null,
                lat: null,
                lng: null,
                is_pickup: true,
                is_valid: true,
                meta: null,
            );
        } else {

            /**
             * 🟢 ЕСЛИ САМОВЫВОЗ
             */
            if ($draft['is_pickup'] ?? false) {
                $delivery = new DeliveryDTO(
                    address: null,
                    lat: null,
                    lng: null,
                    is_pickup: true,
                    is_valid: true,
                    meta: null,
                );
            } else {

                /**
                 * 🟢 ДОСТАВКА (без жёсткой валидации тут)
                 * 👉 ValidateDeliveryAction сделает это позже
                 */
                $delivery = new DeliveryDTO(
                    address: $draft['address'] ?? null,
                    lat: $draft['lat'] ?? null,
                    lng: $draft['lng'] ?? null,
                    is_pickup: false,
                    is_valid: $draft['is_valid'] ?? false,
                    meta: $draft['meta'] ?? null,
                );
            }
        }

        /**
         * 🧠 ВАЖНО: кладём уже DTO
         */
        $request->attributes->set('delivery', $delivery);

        return $next($request);
    }
}