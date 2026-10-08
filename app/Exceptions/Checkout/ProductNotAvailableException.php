<?php

namespace App\Exceptions\Checkout;

class ProductNotAvailableException extends CheckoutException
{
    public function __construct(
        public int $productId,
        public ?string $productName = null
    ) {
        $message = $productName
            ? "Товар \"{$productName}\" недоступен или снят с продажи."
            : "Товар (ID: {$productId}) недоступен для заказа.";

        parent::__construct($message);
    }

    public function code(): string
    {
        return 'PRODUCT_NOT_AVAILABLE';
    }
}
