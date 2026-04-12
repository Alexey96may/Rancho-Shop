<?php

namespace App\DTO;

class CartItemDTO
{
    public function __construct(
        public int $variantId,
        public int $quantity,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            quantity: $data['quantity'],
            variantId: $data['variantId'],
        );
    }
}
