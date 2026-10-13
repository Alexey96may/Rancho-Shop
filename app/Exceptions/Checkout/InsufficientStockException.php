<?php

namespace App\Exceptions\Checkout;

use Illuminate\Validation\ValidationException;

class InsufficientStockException extends CheckoutException
{
    public function __construct(
        public int $productId,
        public ?string $productName = null,
    ) {
        $message = $productName
            ? "Товара \"{$productName}\" нет в наличии в требуемом количестве."
            : "Товара с ID {$productId} нет в наличии в требуемом количестве.";

        $base = ValidationException::withMessages([
            $this->errorKey() => [$message],
        ]);

        parent::__construct($base->validator, $base->response, $base->errorBag);

        $this->message = $message;
    }

    public function code(): string
    {
        return 'INSUFFICIENT_STOCK';
    }

    protected function errorKey(): string
    {
        return 'cart';
    }
}
