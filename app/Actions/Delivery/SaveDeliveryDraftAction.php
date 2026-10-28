<?php

namespace App\Actions\Delivery;

use App\Models\User;

class SaveDeliveryDraftAction
{
    public function handle(array $data, ?User $user): void
    {
        if ($user) {
            $user->update([
                'last_delivery_address' => $data['address'],
                'last_delivery_lat' => $data['lat'],
                'last_delivery_lng' => $data['lng'],
            ]);

            return;
        }

        session(['delivery_draft' => $data]);
    }
}
