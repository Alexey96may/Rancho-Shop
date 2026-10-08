<?php

namespace App\Exceptions\Checkout;

class InsufficientStockException extends CheckoutException
{
    public function __construct(
        public int $productId,
        public ?string $productName = null
    ) {
        $message = $productName
            ? "Товара \"{$productName}\" нет в наличии в требуемом количестве."
            : "Товара с ID {$productId} нет в наличии в требуемом количестве.";

        parent::__construct($message);
    }

    public function code(): string
    {
        return 'INSUFFICIENT_STOCK';
    }
}
