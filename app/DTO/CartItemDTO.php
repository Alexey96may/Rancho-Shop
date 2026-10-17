<?php

namespace App\DTO;

class CartItemDTO
{
    public function __construct(
        public int $variantId,
        public float $quantity,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            variantId: (int) $data['variant_id'],
            quantity: (float) $data['quantity'],
        );
    }
}
