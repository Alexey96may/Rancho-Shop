<?php

namespace App\Actions\Checkout;

use App\Models\User;
use Illuminate\Http\Request;

class GetDeliveryDraftAction
{
    public function handle(?User $user, Request $request): array
    {
        if ($user) {
            return [
                'address' => $user->last_delivery_address ?? $user->defaultDeliveryAddress?->address,
                'lat' => $user->last_delivery_lat ?? $user->defaultDeliveryAddress?->lat,
                'lng' => $user->last_delivery_lng ?? $user->defaultDeliveryAddress?->lng,
                'is_pickup' => false,
                'is_valid' => true,
            ];
        }

        return session('delivery_draft', [
            'address' => null,
            'lat' => null,
            'lng' => null,
            'is_pickup' => false,
            'is_valid' => false,
        ]);
    }
}
