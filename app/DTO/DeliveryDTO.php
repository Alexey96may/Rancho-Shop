<?php

namespace App\DTO;

use Illuminate\Http\Request;

class DeliveryDTO
{
    public function __construct(
        public readonly ?string $address,
        public readonly ?float $lat,
        public readonly ?float $lng,
        public readonly bool $is_pickup,
        public readonly bool $is_valid,
        public readonly ?array $meta = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            address: $request->input('delivery_address'),
            lat: $request->filled('lat') ? (float) $request->input('lat') : null,
            lng: $request->filled('lng') ? (float) $request->input('lng') : null,
            is_pickup: $request->boolean('is_pickup'),
            is_valid: $request->boolean('is_valid', true),
            meta: $request->input('meta')
        );
    }
}
