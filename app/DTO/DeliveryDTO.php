<?php

namespace App\DTO;

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
}