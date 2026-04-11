<?php

namespace App\Exceptions\Checkout;

class ProductNotAvailableException extends CheckoutException
{
    public function __construct(
        public int $productId
    ) {
        parent::__construct("Product {$productId} not available");
    }

    public function code(): string
    {
        return 'PRODUCT_NOT_AVAILABLE';
    }
}
