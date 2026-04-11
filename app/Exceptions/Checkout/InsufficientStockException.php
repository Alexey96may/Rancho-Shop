<?php

namespace App\Exceptions\Checkout;

class InsufficientStockException extends CheckoutException
{
    public function __construct(
        public int $productId
    ) {
        parent::__construct("Not enough stock for product {$productId}");
    }

    public function code(): string
    {
        return 'INSUFFICIENT_STOCK';
    }
}