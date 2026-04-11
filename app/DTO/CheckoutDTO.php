<?php

namespace App\DTO;

use Illuminate\Support\Collection;

class CheckoutDTO
{
    public function __construct(
        public string $customerName,
        public string $customerPhone,
        public ?string $deliveryAddress,
        public ?string $customerComment,

        /** @var Collection<int, CartItemDTO> */
        public Collection $items,
    ) {}
}