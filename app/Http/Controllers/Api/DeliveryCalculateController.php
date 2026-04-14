<?php

namespace App\Http\Controllers\Api;

use App\DTO\DeliveryDTO;
use Illuminate\Http\Request;
use App\Actions\Checkout\ValidateDeliveryAction;

class DeliveryCalculateController
{
    public function __invoke(Request $request, ValidateDeliveryAction $action)
    {
        dd($request->all());
        $dto = new DeliveryDTO(
            address: $request->json('address'),
            lat: (float) $request->json('lat'),
            lng: (float) $request->json('lng'),
            is_pickup: false,
            is_valid: false,
            meta: null,
        );

        $result = $action->handle($dto);

        return response()->json($result);
    }
}